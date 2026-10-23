# How to Fix "File Upload Failed" (validation.uploaded)

If you see an error saying "The file failed to upload. It may be larger than the server allows", it means your PHP configuration has a low upload limit (default is usually 2MB).

## Steps to Increase Upload Limit in Laragon

1.  **Stop** the Laragon server (Click "Stop All").
2.  Click **Menu** (top left) -> **PHP** -> **php.ini**.
    *   This will open the correct configuration file in your editor.
3.  Search for `upload_max_filesize`.
    *   Change it to: `upload_max_filesize = 10M` (or larger).
4.  Search for `post_max_size`.
    *   Change it to: `post_max_size = 10M` (must be >= upload_max_filesize).
5.  Save the file.
6.  **Start** the Laragon server ("Start All").
7.  If you are running `php artisan serve`, stop it (Ctrl+C) and start it again:
    ```bash
    php artisan serve --port=1011
    ```

Now you should be able to upload banners up to 10MB.
