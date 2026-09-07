# storage/ — Phase 0 runtime directories

This folder holds files created while Apache/PHP is running. They are **not** source code.

| Directory | Purpose |
|---|---|
| `logs/` | PHP and Apache error/access logs |
| `rate_limits/` | Brute-force / login rate-limit files from `config/security.php` |
| `uploads/` | Future user or admin uploads |

These three directories are created at runtime (see `scripts/setup-xampp.ps1`) and are gitignored. Do not commit their contents.

This README is tracked so the folder exists in a fresh clone.
