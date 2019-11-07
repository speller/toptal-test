import React from 'react'
import ReactDOM from 'react-dom'
import { ThemeProvider as MuiThemeProvider } from '@material-ui/core/styles'
import createMuiTheme from '@material-ui/core/styles/createMuiTheme'
import Root from './components/Root'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faPlus } from '@fortawesome/free-solid-svg-icons/faPlus'
import { faSignInAlt } from '@fortawesome/free-solid-svg-icons/faSignInAlt'
import { faSignOutAlt } from '@fortawesome/free-solid-svg-icons/faSignOutAlt'
import { faFilter } from '@fortawesome/free-solid-svg-icons/faFilter'
import { faFileExport } from '@fortawesome/free-solid-svg-icons/faFileExport'
import customTheme from './components/common/custom-theme'

library.add(
  faPlus,
  faSignInAlt,
  faSignOutAlt,
  faFilter,
  faFileExport,
)


const theme = createMuiTheme({
  customTheme: customTheme,
  palette: {
    primary: {
      main: customTheme.mainColor,
    },
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

function IndexPage() {
  return (
    <MuiThemeProvider theme={theme}>
      <Root />
    </MuiThemeProvider>
  )
}

// Render the whole application
ReactDOM.render(
  IndexPage(),
  document.getElementById('root')
)
