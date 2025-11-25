import Aura from '@primevue/themes/aura';

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-05-15',

  devtools: { enabled: false },

  modules: [
    '@primevue/nuxt-module',
    '@nuxtjs/tailwindcss',
    '@pinia/nuxt',
    '@vueuse/nuxt',
    '@nuxt/icon',
    'nuxt-laravel-echo',
    'nuxt-signature-pad',
  ],

  primevue: {
    options: {
      ripple: true,
      unstyled: false,
      theme: {
        preset: Aura,
        options: {
          darkModeSelector: '.app-dark'
        }
      }
    },
    components: {
      include: '*',
      exclude: ['Form', 'FormField']
    }
  },

  css: ['~/assets/styles.scss'],

  vite: {
    plugins: [require('vite-svg-loader')()],
  },

  components: [
    {
      path: '~/components',
      pathPrefix: false,
    },
  ],

  imports: {
    dirs: [
      'composables/**',
    ]
  },

  echo: {
    broadcaster: 'reverb',
    key: process.env.NUXT_PUBLIC_REVERB_APP_KEY,
    host: process.env.NUXT_PUBLIC_REVERB_HOST,
    port: Number(process.env.NUXT_PUBLIC_REVERB_PORT),
    scheme: process.env.NUXT_PUBLIC_REVERB_SCHEME as 'http' | 'https',
    transports: ['ws'],
    authentication: {
      mode: 'cookie',
      baseUrl: `http://${process.env.NUXT_PUBLIC_REVERB_HOST}:${process.env.NUXT_PUBLIC_REVERB_PORT ?? 9003}/app`,
      authEndpoint: '/broadcasting/auth',
      csrfEndpoint: '/sanctum/csrf-cookie',
      csrfCookie: 'XSRF-TOKEN',
      csrfHeader: 'X-XSRF-TOKEN',
    },
  },

  app: {
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
      title: 'DIETS v2.0'
    },
  },

  runtimeConfig: {
    public: {
      API_URL: process.env.API_URL,

      API_AUTH: process.env.API_AUTH,

      FEEDBACK_URL: process.env.FEEDBACK_URL,

      REVERB_APP_ID: process.env.NUXT_PUBLIC_REVERB_APP_ID,
      REVERB_APP_KEY: process.env.NUXT_PUBLIC_REVERB_APP_KEY,
      REVERB_APP_SECRET: process.env.NUXT_PUBLIC_REVERB_APP_SECRET,
      REVERB_HOST: process.env.NUXT_PUBLIC_REVERB_HOST,
      REVERB_PORT: process.env.NUXT_PUBLIC_REVERB_PORT,
      REVERB_SCHEME: process.env.NUXT_PUBLIC_REVERB_SCHEME,
    }
  },

  $development: {
    app: {
      head: {
        title: 'Dev | D I E T S v2.0'
      }
    },
    vite: {
      optimizeDeps: {
        include: ['pusher-js'], // or ['nuxt-laravel-echo > pusher-js'] for newer Vite versions
      },
    },
    devServer: {
      host: process.env.HOST,
      port: Number(process.env.PORT) ?? 80
    },
  },
})