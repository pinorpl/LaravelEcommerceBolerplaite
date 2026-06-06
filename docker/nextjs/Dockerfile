FROM node:20-alpine

# Install dependencies for native Node modules
RUN apk add --no-cache libc6-compat

WORKDIR /app

# Copy package files first to leverage Docker layer caching
COPY frontend/package.json frontend/package-lock.json* ./

RUN npm install

# Copy the rest of the frontend source
COPY frontend/ .

EXPOSE 3000

# Start the Next.js development server with hot reloading enabled
CMD ["npm", "run", "dev"]
