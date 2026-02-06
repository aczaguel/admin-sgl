CREATE TABLE IF NOT EXISTS tramite_borrador (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  datos TEXT NOT NULL,
  paso_actual INT DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY user_borrador (user_id),
  INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
