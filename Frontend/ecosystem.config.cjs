module.exports = {
  apps: [
    {
      name: 'DIETS',
      exec_mode: 'cluster',
      instances: 'max',
      script: './.output/server/index.mjs',
      env: {
        PORT: 80,
        NODE_ENV: 'production'
      }
    }
  ]
}