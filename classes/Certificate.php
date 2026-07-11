<?php
/**
 * Certificate.php
 * Handles certificate issuance. A certificate can ONLY be created
 * for a deceased record that has status = 'approved' (business rule).
 */

require_once __DIR__ . '/../config/Database.php';

class Certificate
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Generate a unique certificate number, e.g. DRS-2026-000123
     */
    private function generateCertificateNumber()
    {
        $year = date('Y');
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        return "DRS-{$year}-{$random}";
    }

    /**
     * Issue a certificate for an approved deceased record.
     * Returns the certificate data on success, or an error string.
     */
    public function issue($deceasedId, $issuedBy)
    {
        // 1. Confirm the record exists AND is approved (business rule enforcement)
        $checkSql = "SELECT status FROM deceased WHERE deceased_id = :id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':id' => $deceasedId]);
        $record = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            return "Deceased record not found.";
        }
        if ($record['status'] !== 'approved') {
            return "Certificate can only be issued for approved records.";
        }

        // 2. Prevent duplicate certificates (deceased_id is UNIQUE in the table)
        $existing = $this->findByDeceasedId($deceasedId);
        if ($existing) {
            return $existing; // already issued - just return the existing one
        }

        // 3. Insert the new certificate
        $certNumber = $this->generateCertificateNumber();
        $sql = "INSERT INTO certificates (deceased_id, certificate_number, issued_by)
                VALUES (:deceased_id, :cert_number, :issued_by)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':deceased_id' => $deceasedId,
            ':cert_number' => $certNumber,
            ':issued_by'   => $issuedBy
        ]);

        // Log the action in the audit trail
        $logStmt = $this->db->prepare(
            "INSERT INTO audit_log (user_id, action, table_affected, record_id)
             VALUES (:uid, 'ISSUE_CERTIFICATE', 'certificates', :rid)"
        );
        $logStmt->execute([':uid' => $issuedBy, ':rid' => $this->db->lastInsertId()]);

        return $this->findByDeceasedId($deceasedId);
    }

    /**
     * Fetch a certificate together with the deceased person's details (for printing).
     */
    public function findByDeceasedId($deceasedId)
    {
        $sql = "SELECT cert.*, d.full_name, d.date_of_birth, d.date_of_death,
                       d.place_of_death, c.description AS cause
                FROM certificates cert
                JOIN deceased d ON cert.deceased_id = d.deceased_id
                JOIN causes_of_death c ON d.cause_id = c.cause_id
                WHERE cert.deceased_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $deceasedId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
