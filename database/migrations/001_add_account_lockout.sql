-- Migration: Add account lockout columns for brute force protection
-- Run this in DVWA database to enable account lockout feature

-- Add failed login attempts counter
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS failed_login_attempts INT DEFAULT 0 AFTER password;

-- Add lockout timestamp
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS locked_until DATETIME NULL AFTER failed_login_attempts;

-- Reset any existing failed attempts
UPDATE users SET failed_login_attempts = 0 WHERE failed_login_attempts IS NULL;

-- Optional: Add index for performance on lockout checks
CREATE INDEX IF NOT EXISTS idx_user_lockout ON users(user, locked_until);
