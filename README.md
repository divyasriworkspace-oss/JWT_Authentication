# JWT Token API (PHP)

Simple PHP REST API that demonstrates:
- User registration and login
- JWT-based authentication
- Protected CRUD endpoints for patients

## Project Structure

- app/controllers: Request handlers for auth and patient resources
- app/core: Core utilities (database and router)
- app/helpers: Reusable helpers (JSON response and JWT)
- app/middleware: Request preprocessing and auth guard
- app/models: Database interaction layer
- config: Environment loader
- public: Application entry point

## Notes

- All API responses are JSON.
- Authenticated routes require an Authorization header with a Bearer token.
- Environment variables are loaded from .env.
