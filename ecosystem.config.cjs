module.exports = {
    apps: [
        {
            name: "journa-queue",
            script: "artisan", // اسکریپت اجرایی خودِ فایل artisan است
            interpreter: "/opt/cpanel/ea-php84/root/usr/bin/php", // مفسر را این
            args: "queue:work --sleep=3 --tries=3 --timeout=0",
            cwd: "/home/zetebir/journa/admin", // مسیر اجرای پروژه
            exec_mode: "fork", // حالت اجرا
            watch: false,
            autorestart: true
        }
    ]
}
