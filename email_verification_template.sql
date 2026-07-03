-- =====================================================================
-- Add the dedicated "email_verification" email template.
-- ---------------------------------------------------------------------
-- Registration verification previously reused the `welcome_email` template,
-- which has no {verification_link} placeholder — so the verify link never
-- appeared in the email. Auth_model::sendVerificationEmail() now looks up
-- `email_verification`; this seeds it (admin-editable at
-- Settings → Email Templates). Idempotent: only inserts if missing, and lets
-- the DB assign the id (no hardcoded id → no collision on existing installs).
-- =====================================================================

INSERT INTO `email_templates`
	(`template_key`, `template_name`, `subject`, `body`, `placeholders`, `category`, `status`, `inserted_on`)
SELECT
	'email_verification',
	'Email Verification',
	'Verify your email address - {site_name}',
	'<p>Dear {client_name},</p><p>Thank you for registering with <strong>{site_name}</strong>. Please verify your email address to activate your account.</p><p><a href="{verification_link}" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block;">Verify My Email</a></p><p>Or copy and paste this link into your browser:</p><p>{verification_link}</p><p>If you did not create this account, please ignore this email.</p><p>Regards,<br>{site_name}</p>',
	'{client_name}, {site_name}, {site_url}, {verification_link}',
	'AUTH',
	1,
	NOW()
WHERE NOT EXISTS (
	SELECT 1 FROM `email_templates` WHERE `template_key` = 'email_verification'
);
