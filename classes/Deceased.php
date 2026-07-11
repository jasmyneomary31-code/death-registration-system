<?php
/**
 * Deceased.php
 * Core class of the system - handles registering, approving, and managing death records.
 * Demonstrates: Encapsulation, CRUD, encryption (AES), business rules (approval workflow).
 */

require_once __DIR__ . '/../config/Database.php';

class Deceased
{
    private $db;

    // AES encryption key/method for the national ID field (security requirement)
    // In production this key should come from a secure .env file, not hardcoded.
    private $encryptionKey = "this_is_a_32_character_secret_key!"; // 32+ chars for AES-256
    private $cipherMethod = "AES-256-CBC";

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Encrypt the national ID before storing it.
     * Returns an array [encrypted_data, iv] - both must be saved.
     */
    private function encryptNationalId($plainId)
    {
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = openssl_random_pseudo_bytes($ivLength); // unique IV every time - required for AES-CBC

        $encrypted = openssl_encrypt($plainId, $this->cipherMethod, $this->encryptionKey, 0, $iv);

        return [$encrypted, $iv];
    }

    /**
     * Decrypt the national ID for authorized viewing (e.g. Registrar checking records).
     */
    private function decryptNationalId($encryptedId, $iv)
    {
        if (!$encryptedId) return null;
        return openssl_decrypt($encryptedId, $this->cipherMethod, $this->encryptionKey, 0, $iv);
    }

    /**
     * Register a new death record. Status starts as 'pending' until a Registrar approves it.
     */
    public function register($fullName, $gender, $dob, $dod, $placeOfDeath, $nationalId, $causeId, $registeredBy)
    {
        // Business rule: date of death cannot be before date of birth
        if (strtotime($dod) < strtotime($dob)) {
            return "Date of death cannot be before date of birth.";
        }

        [$encryptedId, $iv] = $this->encryptNationalId($nationalId);

        $sql = "INSERT INTO deceased
                (full_name, gender, date_of_birth, date_of_death, place_of_death,
                 national_id_encrypted, national_id_iv, cause_id, registered_by, status)
                VALUES
                (:full_name, :gender, :dob, :dod, :place,
                 :nid, :iv, :cause_id, :registered_by, 'pending')";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':full_name'     => htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'),
            ':gender'        => $gender,
            ':dob'           => $dob,
            ':dod'           => $dod,
            ':place'         => htmlspecialchars($placeOfDeath, ENT_QUOTES, 'UTF-8'),
            ':nid'           => $encryptedId,
            ':iv'            => $iv,
            ':cause_id'      => $causeId,
            ':registered_by' => $registeredBy
        ]);

        if ($success) {
            $newId = $this->db->lastInsertId();
            $this->logAction($registeredBy, 'CREATE_DECEASED', 'deceased', $newId);
            return true;
        }
        return "Failed to register death record.";
    }

    /**
     * Registrar approves a pending record. Only then can a certificate be issued.
     */
    public function approve($deceasedId, $registrarId)
    {
        $sql = "UPDATE deceased
                SET status = 'approved', approved_by = :registrar_id, approved_at = NOW()
                WHERE deceased_id = :id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([':registrar_id' => $registrarId, ':id' => $deceasedId]);

        if ($success && $stmt->rowCount() > 0) {
            $this->logAction($registrarId, 'APPROVE_DECEASED', 'deceased', $deceasedId);
            return true;
        }
        return "Record not found or already processed.";
    }

    public function reject($deceasedId, $registrarId)
    {
        $sql = "UPDATE deceased
                SET status = 'rejected', approved_by = :registrar_id, approved_at = NOW()
                WHERE deceased_id = :id AND status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([':registrar_id' => $registrarId, ':id' => $deceasedId]);

        if ($success) {
            $this->logAction($registrarId, 'REJECT_DECEASED', 'deceased', $deceasedId);
        }
        return $success;
    }

    /**
     * Fetch all death records with cause description joined in (readable, not just cause_id).
     */
    public function getAll($statusFilter = null)
    {
        $sql = "SELECT d.deceased_id, d.full_name, d.gender, d.date_of_birth, d.date_of_death,
                       d.place_of_death, d.status, c.description AS cause,
                       u.full_name AS registered_by_name
                FROM deceased d
                JOIN causes_of_death c ON d.cause_id = c.cause_id
                JOIN users u ON d.registered_by = u.user_id";

        if ($statusFilter) {
            $sql .= " WHERE d.status = :status";
        }
        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if ($statusFilter) {
            $stmt->execute([':status' => $statusFilter]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch one record by ID, including the decrypted national ID (for authorized staff only).
     */
    public function findById($deceasedId)
    {
        $sql = "SELECT * FROM deceased WHERE deceased_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $deceasedId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            $record['national_id'] = $this->decryptNationalId(
                $record['national_id_encrypted'],
                $record['national_id_iv']
            );
        }
        return $record;
    }

    /**
     * Internal helper: write every important action to the audit_log table.
     */
    private function logAction($userId, $action, $table, $recordId)
    {
        $sql = "INSERT INTO audit_log (user_id, action, table_affected, record_id)
                VALUES (:user_id, :action, :table_name, :record_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'    => $userId,
            ':action'     => $action,
            ':table_name' => $table,
            ':record_id'  => $recordId
        ]);
    }
}
