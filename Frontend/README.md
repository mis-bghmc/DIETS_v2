# 🕹️ Minimal Setup

Make sure to install dependencies:

```bash
# npm
npm install
```

## Development Server

Start the development server on `http://localhost:3000`:

```bash
# npm
npm run dev
```

## Production

Build the application for production:

```bash
# npm
npm run build
```

# ⭐ Production Setup 

## 🚀 PM2 - Process Manager 2

PM2 is a production process manager for Node.js applications — it helps you keep your Nuxt app running continuously, restart it automatically on crashes, and start it on server boot.

### 1️⃣ Build the application

```bash
# npm
npm run build
```

### 2️⃣ Create the PM2 configuration file

At the root of your project (same level as `package.json`), create a file named `ecosystem.config.js` with the following content:

```bash
module.exports = {
  apps: [
    {
      name: 'nuxt-app',            // Name of your app in PM2
      script: './.output/server/index.mjs', // Nuxt 3 production entry
      exec_mode: 'cluster',        // 'cluster' for multi-core, or 'fork' for single instance
      instances: 'max',            // Run on all CPU cores (or set to 1)
      env: {
        NODE_ENV: 'production',
        PORT: 3000                 // Change this port if needed
      }
    }
  ]
}
```

### 3️⃣ Start the application with PM2

```bash
pm2 start ecosystem.config.js
```

### 4️⃣ Verify that the app is running

```bash
pm2 list
```
You should see your app (nuxt-app) in the list with a status of online.

You can also check logs with:
```bash
pm2 logs
```

### 5️⃣ Save the PM2 process list

This ensures PM2 automatically restarts your app after a server reboot:
```bash
pm2 save
```

Then set up startup scripts (only needs to be done once) (Linux/macOS-only)
```bash
pm2 startup
```

### 6️⃣ Managing your app

| Command                | Description            |
| ---------------------- | ---------------------- |
| `pm2 restart nuxt-app` | Restart your app       |
| `pm2 stop nuxt-app`    | Stop the app           |
| `pm2 delete nuxt-app`  | Remove it from PM2     |
| `pm2 logs`             | View real-time logs    |
| `pm2 monit`            | Monitor resource usage |

## 🐳 Docker Stack

This setup uses **Docker Stack** (powered by Docker Swarm) to deploy and manage the application in a **production-grade environment**.  
Unlike regular Docker Compose, Docker Stack allows you to run and scale services across multiple containers — even across multiple servers — with built-in load balancing, auto-restarts, and health monitoring.

### 1️⃣ Create a `Dockerfile` for Nuxt app and Nginx

### 2️⃣ Create a `.dockerignore` file
To avoid copying unnecessary files into the image, add a `.dockerignore` file

### 3️⃣ Build the Docker Image for Nuxt and Nginx

### 4️⃣ Create `docker-stack.yml` file
Inside the root directory, create the `docker-stack.yml` for deploying the stack.

```bash
version: '3.8'

services:
  diets-frontend-nginx:
    image: diets-frontend-nginx:v1
    ports:
      - "80:80"
    configs:
      - source: nginx_conf
        target: /etc/nginx/conf.d/default.conf 
    depends_on:
      - diets-frontend
    deploy:
      replicas: 1
      restart_policy:
        condition: on-failure

  diets-frontend:
    image: diets-frontend:v1
    ports:
      - "3000"
    deploy:
        replicas: 2
        restart_policy:
          condition: on-failure

configs:
  nginx_conf:
    file: ./docker/nginx.conf
```
### 5️⃣ Initialize Docker Swarm (only need to do once)
Before you can use any stack commands, enable Swarm mode:
```bash
docker swarm init
```

### 6️⃣ Deploy the stack
You may use this to start or update a stack:
```bash
docker deploy -c docker-stack.yml nuxt-app
```

### 7️⃣ Managing your stack

| Command                                | Description                            |
| -------------------------------------- | -------------------------------------- |
| `docker swarm init`                    | Enable swarm mode                      |
| `docker stack deploy -c <file> <name>` | Deploy or update a stack               |
| `docker stack ls`                      | List all stacks                        |
| `docker stack services <name>`         | Show services in a stack               |
| `docker stack ps <name>`               | List all tasks (containers) in a stack |
| `docker stack rm <name>`               | Remove the stack                       |
| `docker stack inspect <name>`          | Inspect stack details                  |
| `docker service logs <service>`        | Show logs for a service                |
| `docker service scale <service>=<n>`   | Scale service replicas                 |
| `docker service rollback <service>`    | Roll back to previous version          |


## Deploying of Docker setup in Server

### 1️⃣ Push docker image into Github using ghcr.io
1. Login through ghcr.io using Github PAT 
2. Add appropriate tag to the docker image name
3. Push docker image
```bash
  docker push <image-name>
```
### 2️⃣ Login to server thru SSH and pull docker image
```bash
  docker pull <image-name>
```
### 3️⃣ Create and update the required files
- `docker-stack.yml`
- `nginx.conf`

### 4️⃣ Deploy Stack
```bash
  docker stack deploy -c docker-stack.yml
```
