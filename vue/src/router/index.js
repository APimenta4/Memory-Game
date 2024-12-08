import HomeComponent from '@/components/HomeComponent.vue'
import LaravelTester from '@/components/LaravelTester.vue'
import WebSocketTester from '@/components/WebSocketTester.vue'
import HistoryPage from '@/components/HistoryPage.vue'
import Scoreboard from '@/components/Scoreboard.vue'
import HistoryPageVertical from '@/components/HistoryPageVertical.vue'
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
      path: '/history',
      name: 'history',
      component: HistoryPage 
    },
    {
      path: '/historyvertical',
      name: 'historyvertical',
      component: HistoryPageVertical,
    },
    {
      path: '/scoreboard',
      name: 'scoreboard',
      component: Scoreboard
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
