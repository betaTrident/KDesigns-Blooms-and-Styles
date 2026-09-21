# Phase 8's Cleanup Report

**Date:** 2026-09-08  
**Scope:** Duplicate assets, unused images, git index hygiene, `.gitignore` updates.

## Files deleted (filesystem)

### Repo-root duplicate folder (24 files)
Removed entire `images/` directory at repo root (not DocumentRoot). Duplicated `public/images/`.

### Unused `public/images/` (9 files)
| File | Notes |
|------|-------|
| `IMG_8635 (1).JPG` | Duplicate of referenced `IMG_8635.JPG` |
| `IMG_3341.JPG` | Not in seeds/PHP |
| `IMG_8568.JPG` | Not in seeds/PHP |
| `IMG_8570.JPG` | Not in seeds/PHP |
| `IMG_8585.JPG` | Not in seeds/PHP |
| `IMG_8592.JPG` | Not in seeds/PHP |
| `IMG_8619.JPG` | Not in seeds/PHP |
| `IMG_8627.JPG` | Not in seeds/PHP |
| `IMG_8645.JPG` | Not in seeds/PHP |

**Retained:** 15 images in `public/images/` (all referenced in `database/seeds.sql` or storefront views).

## Git index (`git rm --cached`, not committed)

| Target | Result |
|--------|--------|
| `node_modules/` | Removed ~540 tracked files from index; files remain on disk |
| `package-lock.json` | Removed from index |
| `.vscode/` | Removed `.vscode/launch.json` from index |

## `.gitignore` changes

Added under new "PHP dependencies" section:
- `vendor/`
- `composer.lock`

Existing ignores preserved (`node_modules/`, `package-lock.json`, etc.). Note: `*.lock` already matched `composer.lock`; explicit entry added per Phase 8 spec.

## Reference check

No hardcoded paths to deleted images in PHP or `database/seeds.sql`. Image URLs use `kd_image_url('images/...')` resolving to `public/images/`.

## Concerns

1. **Uncommitted index/worktree changes (~555 entries):** Deletions and `git rm --cached` are staged/unstaged but not committed (per instructions). Next commit should group cleanup + `.gitignore` update.
2. **Tracked repo-root `images/`:** Folder was in git history; removal registers as 24 deletions. Canonical copies remain under `public/images/`.
3. **`vendor/` / `composer.lock`:** Not previously tracked; new ignore rules are preventive only.

## Not modified

- `public/admin_login.php` (kept)
- `public/assets/css/app.css`, `style.css` (kept)
- PHP domain logic, schema, views (unchanged)
