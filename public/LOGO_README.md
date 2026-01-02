# Logo Customization

## Environment-Based Logo Configuration

The login page logo is now **configurable via environment variables**, making it easy to deploy the same codebase for different businesses with different logos.

### Quick Setup (For Different Deployments):

1. **Add your logo file** to the `/public` directory (e.g., `my-company-logo.png`)
2. **Update `.env`** file:
    ```env
    APP_LOGO=my-company-logo
    ```
    _(Note: Don't include the file extension)_
3. **Done!** The system automatically detects and uses your logo

### Automatic Format Detection

The system automatically checks for files in this priority order:

1. `.svg` ✅ (Best - scalable vector graphics)
2. `.png` ✅ (Supports transparency)
3. `.jpg` ✅ (Standard format)
4. `.jpeg` ✅ (Alternative extension)

**Example:**

- If `APP_LOGO=mylogo` is set
- The system checks: `mylogo.svg` → `mylogo.png` → `mylogo.jpg` → `mylogo.jpeg`
- Uses the first file found

### Recommended Specifications:

- **Format**: SVG (preferred) or PNG with transparent background
- **Size**: 512x512 pixels for raster images
- **Aspect Ratio**: 1:1 (square) or similar
- **File Size**: Keep under 500KB for optimal loading

### For Default Logo:

To replace the default logo across all deployments:

1. Replace `/public/logo.svg` or `/public/logo.png`
2. Leave `.env` with `APP_LOGO=logo` (or omit it entirely)

### Multi-Tenant Example:

**Business A Deployment:**

```env
APP_NAME="Business A Inventory"
APP_LOGO=business-a-logo
```

- Upload `business-a-logo.svg` to `/public/`

**Business B Deployment:**

```env
APP_NAME="Business B Inventory"
APP_LOGO=business-b-logo
```

- Upload `business-b-logo.png` to `/public/`

### Technical Details:

- **Configuration:** `config/app.php` (lines 57-80)
- **Middleware:** `app/Http/Middleware/HandleInertiaRequests.php` (line 44)
- **Component:** `resources/js/pages/Auth/Login.vue` (lines 20-22)
- **Logo Display Size:** 96x96 pixels (scales automatically)
