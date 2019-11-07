import React from 'react'
import ReactDOM from 'react-dom'
import { ThemeProvider as MuiThemeProvider } from '@material-ui/core/styles'
import createMuiTheme from '@material-ui/core/styles/createMuiTheme'
import Provider from 'react-redux/es/components/Provider'
import { combineReducers, createStore } from 'redux'
import Root from './components/Root'
// import rootReducers from './components/Root/reducers'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faLock } from '@fortawesome/free-solid-svg-icons/faLock'
import { faPaperPlane } from '@fortawesome/free-solid-svg-icons/faPaperPlane'
import { faTimesCircle } from '@fortawesome/free-solid-svg-icons/faTimesCircle'
import { faBars } from '@fortawesome/free-solid-svg-icons/faBars'
import { faComment } from '@fortawesome/free-solid-svg-icons/faComment'
import { faEnvelope } from '@fortawesome/free-solid-svg-icons/faEnvelope'
import { faBell } from '@fortawesome/free-solid-svg-icons/faBell'
import { faUserCircle } from '@fortawesome/free-solid-svg-icons/faUserCircle'
import { faSearch } from '@fortawesome/free-solid-svg-icons/faSearch'
import { faEllipsisV } from '@fortawesome/free-solid-svg-icons/faEllipsisV'
import { faPlus } from '@fortawesome/free-solid-svg-icons/faPlus'
import { faStar } from '@fortawesome/free-solid-svg-icons/faStar'
import { faSignInAlt } from '@fortawesome/free-solid-svg-icons/faSignInAlt'
import { faSignOutAlt } from '@fortawesome/free-solid-svg-icons/faSignOutAlt'
import { faListUl } from '@fortawesome/free-solid-svg-icons/faListUl'
import { faFilter } from '@fortawesome/free-solid-svg-icons/faFilter'
import customTheme from './components/common/custom-theme'

library.add(
  faLock,
  faPaperPlane,
  faTimesCircle,
  faBars,
  faComment,
  faEnvelope,
  faBell,
  faUserCircle,
  faSearch,
  faEllipsisV,
  faPlus,
  faStar,
  faSignInAlt,
  faSignOutAlt,
  faListUl,
  faFilter,
)


const theme = createMuiTheme({
  customTheme: customTheme,
  palette: {
    primary: {
      main: customTheme.mainColor,
    },
    // secondary: pink,
    // error: red,
    // Used by `getContrastText()` to maximize the contrast between the background and
    // the text.
    // contrastThreshold: 3,
    // Used to shift a color's luminance by approximately
    // two indexes within its tonal palette.
    // E.g., shift from Red 500 to Red 300 or Red 700.
    // tonalOffset: 0.2,
  },
  typography: {
    useNextVariants: true,
    fontFamily: [
      'Roboto',
      'Open Sans',
      '-apple-system',
      'BlinkMacSystemFont',
      '"Segoe UI"',
      '"Helvetica Neue"',
      'Arial',
      'sans-serif',
      '"Apple Color Emoji"',
      '"Segoe UI Emoji"',
      '"Segoe UI Symbol"',
    ].join(','),
  },
})

function IndexPage(store) {
  return (
    <Provider store={store}>
      <MuiThemeProvider theme={theme}>
        <Root />
      </MuiThemeProvider>
    </Provider>
  )
}

// Initialize reducers and sagas for the app
const reducers = {
  // root: rootReducers,
  // loginForm: loginFormReducers,
  // mainPage: chatFormReducers,
  // message: messageReducers,
}

// Create Redux store with all the enhancements
const store = createStore(
  Object.keys(reducers).length ? combineReducers(reducers) : state => ({...state}),
  {}
)

// Render the whole application
ReactDOM.render(
  IndexPage(store),
  document.getElementById('root')
)
