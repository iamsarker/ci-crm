-- Update Resell.biz to PRODUCTION settings
-- IMPORTANT: Replace YOUR_RESELLER_ID and YOUR_API_KEY with your actual credentials

UPDATE `dom_registers`
SET
  `domain_check_api` = 'https://httpapi.com/api/domains/available.json?',
  `suggestion_api` = 'https://httpapi.com/api/domains/v5/suggest-names.json?',
  `domain_reg_api` = 'https://httpapi.com/api/domains/register.xml?',
  `api_base_url` = 'https://httpapi.com/api/domains',
  `auth_userid` = 'YOUR_RESELLER_ID',      -- Replace with your actual Reseller ID
  `auth_apikey` = 'YOUR_API_KEY',          -- Replace with your actual API Key
  `is_selected` = 1
WHERE `id` = 1 AND `name` = 'Resell.Biz';

-- Verify the update
SELECT id, name, domain_check_api, auth_userid, is_selected
FROM dom_registers
WHERE status = 1;
