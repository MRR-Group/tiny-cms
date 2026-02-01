FROM node:20-alpine

# Arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

# Enable corepack and install pnpm globally
RUN corepack enable && \
    corepack prepare pnpm@latest --activate

# Create user with matching host UID/GID
RUN addgroup -g ${GROUP_ID} nodeuser && \
    adduser -D -u ${USER_ID} -G nodeuser nodeuser

WORKDIR /app

USER nodeuser

EXPOSE 5173 6006
