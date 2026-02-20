import { defineConfig } from 'vite'
import uni from '@dcloudio/vite-plugin-uni'

// 关键：必须保留 uni() 插件，否则 .vue 无法被正确解析
export default defineConfig({
  plugins: [uni()],
  server: {
    proxy: {
      '/api': {
        target: 'https://apidocs.hahahaxinli.com',
        changeOrigin: true,
        secure: true
      }
    }
  }
})
