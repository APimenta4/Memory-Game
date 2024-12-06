import HomeComponent from '@/components/HomeComponent.vue'
import LaravelTester from '@/components/LaravelTester.vue'
import WebSocketTester from '@/components/WebSocketTester.vue'
import HistoryPage from '@/components/HistoryPage.vue'
import { createRouter, createWebHistory } from 'vue-router'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeComponent
    },
    {
      path: '/history', // The URL path for the new page
      name: 'history',
      component: HistoryPage // The new page component
    },
    {
      path: '/testers',
      children: [
        {
          path: 'laravel',
          component: LaravelTester
        },
        {
          path: 'websocket',
          component: WebSocketTester
        },
      ]
    }
  ]
})

export default router
