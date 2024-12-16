import HomeComponent from '@/components/HomeComponent.vue'
import LaravelTester from '@/components/LaravelTester.vue'
import WebSocketTester from '@/components/WebSocketTester.vue'

import SinglePlayerPage from '@/components/singlePlayer/SinglePlayerPage.vue'
import SinglePlayerGame from '@/components/singlePlayer/SinglePlayerGame.vue'

import HistoryPage from '@/components/HistoryPage.vue'
import GlobalScoreboard from '@/components/GlobalScoreboard.vue'
import PersonalScoreboard from '@/components/PersonalScoreboard.vue'
import MultiPlayerGames from '@/components/multiPlayer/MultiPlayerGames.vue'
import Game from '@/components/multiPlayer/Game.vue'
import BuyCoinsPage from '@/components/BuyCoinsPage.vue';
import TransactionsHistoryPage from '@/components/TransactionsHistoryPage.vue';
import StatisticsPersonalPage from '@/components/StatisticsPersonalPage.vue';


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
        path: '/singleplayer',
        name: 'singleplayer',
        component: SinglePlayerPage,
    },
    {
        path: '/singleplayer/game',
        name: 'singlePlayerGame',
        component: SinglePlayerGame,
        props: route => ({
            gameId: route.query.id || null,
        }),
    },
    {
      path: '/multiplayer',
      name: 'multiplayer',
      component: MultiPlayerGames,
    },
    {
      path: '/multiplayer/game',
      name: 'multiPlayerGame',
      component: Game,
    },
    {
      path: '/history', // The URL path for the new page
      name: 'history',
      component: HistoryPage,
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
    path: '/transactions/buy-coins',
    name: 'buyCoins',
    component: BuyCoinsPage
    },
    {
    path: '/transactions/history',
    name: 'transactionsHistory',
    component: TransactionsHistoryPage
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
    },
    {
      path: '/transactions/buy-coins',
      name: 'buyCoins',
      component: BuyCoinsPage
    },
    {
      path: '/transactions/history',
      name: 'transactionsHistory',
      component: TransactionsHistoryPage
    },
    {
      path: '/statistics/personal',
      name: 'indexPersonalStatistics',
      component: StatisticsPersonalPage
    }
  ]
})

export default router
