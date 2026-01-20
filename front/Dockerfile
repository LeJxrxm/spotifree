# Syntax for Dokploy/Production
FROM node:20-alpine AS builder

WORKDIR /app

# Install dependencies
COPY package.json package-lock.json ./
RUN npm install

# Copy source
COPY . .

# Build application
RUN npm run build

# Runner stage
FROM node:20-alpine AS runner

WORKDIR /app

# Copy built application and specific dependencies if needed
# Nuxt 4 (Nitro) build output is standalone usually
COPY --from=builder /app/.output ./.output

# Environment variables
ARG NUXT_PUBLIC_API_BASE
ENV NUXT_PUBLIC_API_BASE=${NUXT_PUBLIC_API_BASE}
ENV NUXT_HOST=0.0.0.0
ENV NUXT_PORT=3000
ENV NODE_ENV=production

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
