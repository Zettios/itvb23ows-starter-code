# Start from base
FROM mysql:latest

# Run db script
COPY ./hive.sql ./docker-entrypoint-initdb.d/

# Expose port
EXPOSE 3306