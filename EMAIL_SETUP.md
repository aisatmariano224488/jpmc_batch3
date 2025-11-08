# Email System Setup Guide

This guide explains how to set up and configure the new PHP mailer system that replaces the Brevo API.

## Overview

The email system has been updated to use PHP's built-in `mail()` function instead of the Brevo API. This provides several benefits:

- **No external dependencies**: No need for API keys or external services
- **Better control**: Direct control over email sending
- **Cost-effective**: No monthly fees for email services
- **Improved design**: Modern, responsive email templates

## Files Modified

1. **`includes/email_config.php`** - New email configuration file
2. **`contact.php`** - Updated to use PHP mailer
3. **`admin/admin_inquiries.php`** - Updated to use PHP mailer
4. **`test_email.php`** - Test script to verify email functionality

## Configuration

### 1. Email Settings

Edit `includes/email_config.php` and update the following settings:

```php
$email_config = array(
    'from_name' => 'James Polymers Support',
    'from_email' => 'noreply@james-polymers.com', // Change to your domain
    'reply_to' => 'support@james-polymers.com',   // Change to your support email
    'admin_email' => 'danielrossevia@gmail.com',    // Admin notification email
    'company_name' => 'James Polymers Manufacturing Corporation',
    'company_website' => 'https://www.james-polymers.com',
    'company_address' => '16 Aguinaldo HI-Way Panapaan II, City of Bacoor, Cavite, Philippines',
    'company_phone' => '+63 123 456 7890'
);
```

### 2. SMTP Configuration (Optional)

If you want to use SMTP instead of the local mail server, update the SMTP settings:

```php
$smtp_config = array(
    'use_smtp' => true, // Set to true to use SMTP
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password',
    'smtp_encryption' => 'tls'
);
```

## Server Configuration

### For XAMPP (Windows)

1. **Enable SMTP in XAMPP**:
   - Open XAMPP Control Panel
   - Click on "Config" for Apache
   - Select "php.ini"
   - Find the `[mail function]` section
   - Uncomment and configure:
   ```ini
   [mail function]
   SMTP = localhost
   smtp_port = 25
   sendmail_from = your-email@yourdomain.com
   ```

2. **Install Mercury Mail Server** (Optional):
   - Download Mercury from the XAMPP website
   - Install and configure it for local email testing

### For Linux/Unix Servers

1. **Install and configure a mail server**:
   ```bash
   # Ubuntu/Debian
   sudo apt-get install postfix
   
   # CentOS/RHEL
   sudo yum install postfix
   ```

2. **Configure Postfix**:
   ```bash
   sudo nano /etc/postfix/main.cf
   ```

3. **Restart the mail service**:
   ```bash
   sudo systemctl restart postfix
   ```

### For Shared Hosting

Most shared hosting providers have mail servers pre-configured. You may need to:

1. Contact your hosting provider to enable mail sending
2. Use their SMTP settings if provided
3. Ensure your domain has proper SPF/DKIM records

## Testing the Email System

### 1. Run the Test Script

Visit `http://your-domain.com/test_email.php` to run comprehensive tests:

- ✅ Mail function availability
- ✅ Email configuration
- ✅ Template creation
- ✅ Server configuration
- ✅ Log file access

### 2. Test Contact Form

1. Fill out the contact form on your website
2. Check if the admin receives the notification email
3. Check the log file at `logs/email_debug.log`

### 3. Test Admin Reply

1. Log into the admin panel
2. Reply to an inquiry
3. Check if the customer receives the reply email

## Email Templates

The system includes three types of email templates:

### 1. Contact Form Notification
- Sent to admin when someone submits the contact form
- Includes all form data in a structured format
- Features priority badges and call-to-action buttons

### 2. Admin Reply
- Sent to customers when admin replies to their inquiry
- Clean, professional design
- Includes company branding

### 3. Default Template
- Fallback template for other email types

## Troubleshooting

### Common Issues

1. **Emails not sending**:
   - Check server mail configuration
   - Verify firewall settings
   - Test with `test_email.php`

2. **Emails going to spam**:
   - Configure SPF records for your domain
   - Set up DKIM authentication
   - Use a reputable SMTP service

3. **SMTP errors**:
   - Verify SMTP credentials
   - Check port and encryption settings
   - Ensure SMTP server is accessible

4. **Permission errors**:
   - Check web server permissions
   - Verify log file write access
   - Ensure mail server is running

### Debugging

1. **Check the log file**:
   ```bash
   tail -f logs/email_debug.log
   ```

2. **Enable error reporting**:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Test mail function directly**:
   ```php
   $result = mail('test@example.com', 'Test', 'Test message');
   var_dump($result);
   ```

## Security Considerations

1. **Email validation**: Always validate email addresses
2. **Rate limiting**: Implement rate limiting to prevent spam
3. **Input sanitization**: Sanitize all user inputs
4. **SPF/DKIM**: Configure proper email authentication
5. **HTTPS**: Use HTTPS for secure form submission

## Performance Optimization

1. **Email queuing**: For high-volume sites, consider implementing email queuing
2. **Template caching**: Cache email templates for better performance
3. **Async sending**: Send emails asynchronously to avoid blocking the user experience

## Migration from Brevo API

The migration from Brevo API to PHP mailer is complete. The system now:

- ✅ Uses PHP's built-in mail function
- ✅ Has improved email templates
- ✅ Includes comprehensive logging
- ✅ Provides better error handling
- ✅ Offers SMTP configuration options

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review the log files
3. Test with the provided test script
4. Contact your hosting provider for mail server issues

## Files to Update

Remember to update these email addresses in your configuration:

- `from_email`: Your domain's email address
- `reply_to`: Your support email address
- `admin_email`: Email where you want to receive notifications

## Next Steps

1. Configure your email settings
2. Test the system with `test_email.php`
3. Update your domain's DNS records (SPF, DKIM)
4. Monitor email delivery and spam folder placement
5. Consider implementing email queuing for production use 