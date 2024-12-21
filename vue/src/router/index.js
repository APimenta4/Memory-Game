import HomeComponent from '@/components/HomeComponent.vue'
import WebSocketTester from '@/components/WebSocketTester.vue'

import SinglePlayerPage from '@/components/singlePlayer/SinglePlayerPage.vue'
import SinglePlayerGame from '@/components/singlePlayer/SinglePlayerGame.vue'

import HistoryPage from '@/components/HistoryPage.vue'
import GlobalScoreboard from '@/components/GlobalScoreboard.vue'
import PersonalScoreboard from '@/components/PersonalScoreboard.vue'
import MultiPlayerGames from '@/components/multiPlayer/MultiPlayerGames.vue'
import Game from '@/components/multiPlayer/Game.vue'
import TransactionsHistoryPage from '@/components/TransactionsHistoryPage.vue';
import StatisticsAnonymousPage from '@/components/StatisticsAnonymousPage.vue'
import StatisticsAdminPage from '@/components/StatisticsAdminPage.vue'
import StatisticsPersonalPage from '@/components/StatisticsPersonalPage.vue'
import UsersPage from '@/components/UsersPage.vue'
import Register from '@/components/auth/Register.vue'

import Login from '@/components/auth/Login.vue'

import ProfilePageEdit from '@/components/ProfilePageEdit.vue'

import ProfilePage from '@/components/ProfilePage.vue'

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
      component: SinglePlayerPage
    },
    {
      path: '/singleplayer/game',
      name: 'singlePlayerGame',
      component: SinglePlayerGame
    },
    {
      path: '/multiplayer',
      name: 'multiplayer',
      component: MultiPlayerGames
    },
    {
      path: '/multiplayer/game',
      name: 'multiPlayerGame',
      component: Game
    },
    {
      path: '/history',
      name: 'history',
      component: HistoryPage
    },
    {
      path: '/login',
      name: 'login',
      component: Login
    },
    {
      path: '/register',
      name: 'register',
      component: Register
    },
    {
      path: '/profile',
      name: 'profile',
      component: ProfilePage
    },
    {
      path: '/profile/edit',
      name: 'profileEdit',
      component: ProfilePageEdit
    },
    {
      path: '/scoreboard',
      children: [
        {
          path: 'global',
          name: 'scoreboardGlobal',
          component: GlobalScoreboard
        },
        {
          path: 'personal',
          name: 'scoreboardPersonal',
          component: PersonalScoreboard
        }
      ]
    },
    {
      path: '/transactions/history',
      name: 'transactionsHistory',
      component: TransactionsHistoryPage
    },
    {
      path: '/statistics/admin',
      name: 'indexAdminStatistics',
      component: StatisticsAdminPage
    },
    {
      path: '/statistics',
      name: 'indexGlobalStatistics',
      component: StatisticsAnonymousPage
    },
    {
      path: '/statistics/personal',
      name: 'indexPersonalStatistics',
      component: StatisticsPersonalPage
    },
    {
      path: '/users',
      name: 'users',
      component: UsersPage
    },
    {
      path: '/:catchAll(.*)',
      redirect: '/',
   },
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
    // Can't access if you are logged in
    case 'login':
      if(storeAuth.user) {
        next({ name: 'profile' })
        return
      }
      break

    case 'register':
      if(storeAuth.user && storeAuth.user?.type !== 'A') {
        next({ name: 'profile' })
        return
      }
      break
      
    // Can't access if you are not logged in
    case 'profile':
    case 'profileEdit':
    case 'transactionsHistory': 
    case 'history':
      if(!storeAuth.user) {
        next({ name: 'login' })
        return
      }
      break

    // Can't acess if you are admin
    case 'singleplayer':
    case 'singlePlayerGame':
    case 'multiplayer':
        if(storeAuth.user && storeAuth.user.type === 'A'){
            next({ name: 'home' })
            return
        }
        break

    // Can't access if you are not logged in or you are an admin
    case 'scoreboardPersonal':
    case 'indexPersonalStatistics':
    case 'multiPlayerGame':
      if(!storeAuth.user) {
        next({ name: 'login' })
        return
      }
      if(storeAuth.user.type == 'A'){
        next({ name: 'home' })
        return
      }
      break

    // Can't access if you are not an admin
    case 'users':
    case 'indexAdminStatistics':
      if(storeAuth.user.type !== 'A') {
        next({ name: 'home' })
        return
      }
      break

    // Anyone can access
    
    // case 'scoreboardGlobal': 
    // case 'home':
    // case 'indexGlobalStatistics':
  }


  next()
})

export default router
