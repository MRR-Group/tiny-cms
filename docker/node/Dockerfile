FROM node:20-slim

# Arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

# Enable corepack and install pnpm globally
RUN corepack enable && \
    corepack prepare pnpm@latest --activate && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
        git \
        procps \
        # Playwright dependencies
        libnss3 \
        libatk1.0-0 \
        libatk-bridge2.0-0 \
        libcups2 \
        libdrm2 \
        libxkbcommon0 \
        libxcomposite1 \
        libxdamage1 \
        libxfixes3 \
        libxrandr2 \
        libgbm1 \
        libpango-1.0-0 \
        libcairo2 \
        libasound2 \
        libatspi2.0-0 \
        libwayland-client0 && \
    rm -rf /var/lib/apt/lists/*

# Create user with matching host UID/GID
RUN if getent passwd ${USER_ID} > /dev/null; then \
        userdel $(getent passwd ${USER_ID} | cut -d: -f1); \
    fi && \
    if getent group ${GROUP_ID} > /dev/null; then \
        groupdel $(getent group ${GROUP_ID} | cut -d: -f1) 2>/dev/null || true; \
    fi && \
    groupadd -g ${GROUP_ID} nodeuser && \
    useradd -u ${USER_ID} -g nodeuser -m nodeuser

WORKDIR /app

USER nodeuser

EXPOSE 5173 6006
