import HomeComponent from '@/components/HomeComponent.vue'
import LaravelTester from '@/components/LaravelTester.vue'
import WebSocketTester from '@/components/WebSocketTester.vue'
import HistoryPage from '@/components/HistoryPage.vue'
import HistoryPageVertical from '@/components/HistoryPageVertical.vue'
import GlobalScoreboard from '@/components/GlobalScoreboard.vue'
import PersonalScoreboard from '@/components/PersonalScoreboard.vue'
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
      path: '/historyVertical',
      name: 'historyVertical',
      component: HistoryPageVertical,
    },
    {
      path: '/scoreboard',
      children:[
        {
          path: 'global',
          name: 'scoreboardGlobal',
          component: GlobalScoreboard,
        },
        {
          path: 'personal',
          name: 'scoreboardPersonal',
          component: PersonalScoreboard,
        },
      ]
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
