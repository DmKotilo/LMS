# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Получите токен через <code>POST /api/auth/login</code> (email и пароль). Передавайте заголовок <code>Authorization: Bearer {TOKEN}</code>.
