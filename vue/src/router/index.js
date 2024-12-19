import HomeComponent from '@/components/HomeComponent.vue'
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
import UsersPage from '@/components/UsersPage.vue';
import Register from '@/components/auth/Register.vue';

import Login from '@/components/auth/Login.vue';

import ProfilePageEdit from '@/components/ProfilePageEdit.vue';

import ProfilePage from '@/components/ProfilePage.vue';

import { createRouter, createWebHistory } from 'vue-router'

import { useAuthStore } from '@/stores/auth'

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
      path: '/history',
      name: 'history',
      component: HistoryPage,
    },
    {
      path: '/login', 
      name: 'login',
      component: Login,
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfilePage,
    },
    {
      path: '/profile/edit',
      name: 'profileEdit',
      component: ProfilePageEdit,
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
          path: 'websocket',
          component: WebSocketTester
        },
      ]
    },
    {
      path: '/users',
      name: 'users',
      component: UsersPage,
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

let handlingFirstRoute = true

router.beforeEach(async (to, from, next) => {
    const storeAuth = useAuthStore()
    if (handlingFirstRoute) {
        handlingFirstRoute = false
        await storeAuth.restoreToken()
    }

    
    switch (to.name) {
        case 'login':
        case 'register':
            if (storeAuth.user) {
                next({ name: 'home' })
                return
            }
            break
    }


    // routes that are only accessible when user is logged in
    if (((to.name == 'history') || (to.name == 'profile') || (to.name == 'profileEdit') || (to.name == 'scoreboardGlobal') || (to.name == 'scoreboardPersonal') || (to.name == 'buyCoins') || (to.name == 'transactionsHistory') ) && (!storeAuth.user)) {
        next({ name: 'login' })
        return
    }
    // all other routes are accessible to everyone, including anonymous users
    next()
})


export default router
