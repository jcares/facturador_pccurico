-- Secrets Vault Schema for facturador_pccurico
-- This migration creates the foundation for encrypted credential storage

-- Table: secrets (encrypted credentials storage)
CREATE TABLE IF NOT EXISTS secrets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(255) UNIQUE NOT NULL COMMENT 'Unique identifier for the secret',
    secret_type ENUM('certificate', 'credential', 'api_key', 'database', 'config') NOT NULL COMMENT 'Type of secret',
    encrypted_value LONGBLOB NOT NULL COMMENT 'AES-256-GCM encrypted secret',
    iv VARCHAR(32) NOT NULL COMMENT 'Initialization vector (hex encoded)',
    tag VARCHAR(32) NOT NULL COMMENT 'Authentication tag (hex encoded)',
    metadata JSON COMMENT 'Additional metadata (encrypted filename, expiration, etc)',
    created_by VARCHAR(255) COMMENT 'User/service that created this secret',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at DATETIME COMMENT 'Optional expiration date',
    is_active BOOLEAN DEFAULT TRUE,
    last_rotated_at DATETIME,
    INDEX idx_key_name (key_name),
    INDEX idx_secret_type (secret_type),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Encrypted secrets vault - stores sensitive credentials and configurations';

-- Table: audit_logs (comprehensive audit trail)
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    action VARCHAR(50) NOT NULL COMMENT 'Action performed: READ, CREATE, UPDATE, DELETE, ROTATE',
    secret_key_name VARCHAR(255) COMMENT 'Secret accessed (null if not applicable)',
    user_id INT COMMENT 'User performing the action',
    user_name VARCHAR(255) COMMENT 'Username/service name',
    ip_address VARCHAR(45) COMMENT 'IP address (IPv4 or IPv6)',
    user_agent TEXT COMMENT 'HTTP User-Agent',
    result ENUM('success', 'failure', 'denied') DEFAULT 'success' COMMENT 'Result of the action',
    error_message VARCHAR(500) COMMENT 'Error details if action failed',
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_id VARCHAR(36) COMMENT 'Correlation ID for request tracing',
    details JSON COMMENT 'Additional context (before/after values, reason for access)',
    INDEX idx_action (action),
    INDEX idx_secret_key_name (secret_key_name),
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_request_id (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Complete audit trail for all secret vault operations';

-- Table: key_rotation_log (track encryption key changes)
CREATE TABLE IF NOT EXISTS key_rotation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rotation_id VARCHAR(36) UNIQUE NOT NULL COMMENT 'UUID for this rotation',
    old_key_version INT COMMENT 'Previous key version',
    new_key_version INT NOT NULL COMMENT 'New key version',
    secrets_rotated INT DEFAULT 0 COMMENT 'Number of secrets re-encrypted',
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    error_details TEXT COMMENT 'Error information if rotation failed',
    performed_by VARCHAR(255) COMMENT 'User/service that initiated rotation',
    INDEX idx_status (status),
    INDEX idx_completed_at (completed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Encryption key rotation history and status';

-- Table: certificates_store (certificate metadata and management)
CREATE TABLE IF NOT EXISTS certificates_store (
    id INT PRIMARY KEY AUTO_INCREMENT,
    certificate_name VARCHAR(255) UNIQUE NOT NULL COMMENT 'Certificate identifier',
    secret_key_name VARCHAR(255) NOT NULL COMMENT 'Reference to secrets table',
    certificate_type ENUM('pfx', 'pem', 'cer', 'key') NOT NULL,
    thumbprint VARCHAR(255) UNIQUE COMMENT 'Certificate thumbprint (SHA-1)',
    issuer VARCHAR(500),
    subject VARCHAR(500),
    valid_from DATETIME,
    valid_until DATETIME,
    is_revoked BOOLEAN DEFAULT FALSE,
    usage ENUM('signing', 'encryption', 'mutual_tls') DEFAULT 'signing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by VARCHAR(255),
    INDEX idx_certificate_name (certificate_name),
    INDEX idx_thumbprint (thumbprint),
    INDEX idx_valid_until (valid_until),
    INDEX idx_is_revoked (is_revoked),
    FOREIGN KEY (secret_key_name) REFERENCES secrets(key_name) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Certificate metadata and validation information';

-- Table: access_policies (role-based access control)
CREATE TABLE IF NOT EXISTS access_policies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    policy_name VARCHAR(255) UNIQUE NOT NULL,
    role_name VARCHAR(255) NOT NULL,
    secret_pattern VARCHAR(255) NOT NULL COMMENT 'Regex pattern for allowed secrets',
    allowed_actions SET('READ', 'CREATE', 'UPDATE', 'DELETE', 'ROTATE') DEFAULT 'READ',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(255),
    INDEX idx_role_name (role_name),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Access control policies for secret operations';

-- Stored Procedure: Log audit event
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS audit_log_event(
    IN p_action VARCHAR(50),
    IN p_secret_key_name VARCHAR(255),
    IN p_user_id INT,
    IN p_user_name VARCHAR(255),
    IN p_ip_address VARCHAR(45),
    IN p_result VARCHAR(20),
    IN p_error_message VARCHAR(500),
    IN p_request_id VARCHAR(36),
    IN p_details JSON
)
BEGIN
    INSERT INTO audit_logs (
        action, secret_key_name, user_id, user_name, ip_address,
        result, error_message, request_id, details, timestamp
    ) VALUES (
        p_action, p_secret_key_name, p_user_id, p_user_name, p_ip_address,
        p_result, p_error_message, p_request_id, p_details, NOW()
    );
END$$
DELIMITER ;

-- Index for performance on common queries
CREATE INDEX idx_secrets_active_not_expired ON secrets(is_active, expires_at);
CREATE INDEX idx_audit_logs_by_date_range ON audit_logs(timestamp, action);
