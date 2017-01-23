<?php

namespace craft\migrations;

use Craft;
use craft\db\Migration;

/**
 * m150617_213829_update_email_settings migration.
 */
class m150617_213829_update_email_settings extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $systemSettingsService = Craft::$app->getSystemSettings();
        $oldSettings = $systemSettingsService->getSettings('email');

        if (isset($oldSettings['emailAddress']) && isset($oldSettings['senderName']) && isset($oldSettings['protocol'])) {
            // Start assembling the new settings
            $settings = [
                'fromEmail' => $oldSettings['emailAddress'],
                'fromName' => $oldSettings['senderName'],
                'template' => $oldSettings['template'] ?? null,
            ];

            // Start assembling the Mailer config
            $mailerConfig = [
                'class' => 'craft\mail\Mailer',
                'from' => [$settings['fromEmail'] => $settings['fromName']],
                'template' => $settings['template'],
            ];

            // Protocol-specific stuff
            switch ($oldSettings['protocol']) {
                case 'sendmail':
                    $settings['transportType'] = 'craft\mail\transportadapters\Sendmail';
                    $mailerConfig['transport'] = [
                        'class' => 'Swift_SendmailTransport'
                    ];
                    break;
                case 'smtp':
                    $settings['transportType'] = 'craft\mail\transportadapters\Smtp';
                    $settings['transportSettings'] = [
                        'host' => $oldSettings['host'] ?? null,
                        'port' => $oldSettings['port'] ?? null,
                        'useAuthentication' => $oldSettings['smtpAuth'] ?? false,
                        'username' => $oldSettings['username'] ?? null,
                        'password' => $oldSettings['password'] ?? null,
                        'encryptionMethod' => isset($oldSettings['smtpSecureTransportType']) && $oldSettings['smtpSecureTransportType'] !== 'none' ? $oldSettings['smtpSecureTransportType'] : null,
                        'timeout' => $oldSettings['timeout'] ?? 10,
                    ];
                    $mailerConfig['transport'] = [
                        'class' => 'Swift_SmtpTransport',
                        'host' => $settings['transportSettings']['host'],
                        'port' => $settings['transportSettings']['port'],
                        'timeout' => $settings['transportSettings']['timeout'],
                    ];
                    if ($settings['transportSettings']['useAuthentication']) {
                        $mailerConfig['username'] = $settings['transportSettings']['username'];
                        $mailerConfig['password'] = $settings['transportSettings']['password'];
                    }
                    if ($settings['transportSettings']['encryptionMethod']) {
                        $mailerConfig['encryption'] = $settings['transportSettings']['encryptionMethod'];
                    }
                    break;
                case 'gmail':
                    $settings['transportType'] = 'craft\mail\transportadapters\Gmail';
                    $settings['transportSettings'] = [
                        'username' => $oldSettings['username'] ?? null,
                        'password' => $oldSettings['password'] ?? null,
                        'timeout' => $oldSettings['timeout'] ?? 10,
                    ];
                    $mailerConfig['transport'] = [
                        'class' => 'Swift_SmtpTransport',
                        'host' => 'smtp.gmail.com',
                        'port' => 465,
                        'encryption' => 'ssl',
                        'username' => $settings['transportSettings']['username'],
                        'password' => $settings['transportSettings']['password'],
                        'timeout' => $settings['transportSettings']['timeout'],
                    ];
                    break;
                default:
                    $settings['transportType'] = 'craft\mail\transportadapters\Php';
                    $mailerConfig['transport'] = [
                        'class' => 'Swift_MailTransport'
                    ];
            }

            // Save the new settings
            $systemSettingsService->saveSettings('email', $settings);
            $systemSettingsService->saveSettings('mailer', $mailerConfig);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m150617_213829_update_email_settings cannot be reverted.\n";

        return false;
    }
}