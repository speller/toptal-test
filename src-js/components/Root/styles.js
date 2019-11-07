import makeStyles from '@material-ui/core/styles/makeStyles'
import { lighten } from '@material-ui/core/styles'
import scrollBarStyles from '../common/scrollbar-styles'

export default makeStyles(theme => ({
  '@global': {
    html: {
      height: '100%',
    },
    body: {
      height: '100%',
    },
    '#root': {
      height: '100%',
    },
  },

  appBar: {
  },

  appToolBar: {
    backgroundColor: theme.palette.background.default,
    color: lighten(theme.palette.text.primary, 0.15),
  },

  logo: {
    width: '81px',
    height: '50px',
    marginRight: theme.spacing(2),
  },

  title: {
    color: theme.customTheme.mainColor,
    textAlign: 'center',
    lineHeight: '1rem',
    '& span': {
      color: theme.customTheme.mainColor2,
      marginLeft: theme.spacing(0.5),
    },
    '& i': {
      fontStyle: 'normal',
      color: lighten(theme.palette.text.primary, 0.5),
      fontSize: '0.7rem',
    },
  },

  grow: {
    flexGrow: 1,
  },

  signInTitle: {
    marginBottom: theme.spacing(1),
  },

  signedInTitle: {
    paddingRight: theme.spacing(1),
  },

  root: {
    width: '100%',
    height: '100%',
    display: 'flex',
    flexDirection: 'column',
  },

  contentPadding: {
    minHeight: '56px',
    [theme.breakpoints.up('sm')]: {
      minHeight: '64px',
    },
  },

  signInBlock: {
    margin: 'auto',
    width: '300px',
    textAlign: 'center',
  },

  signInBtnIcon: {
    marginLeft: theme.spacing(1),
  },

  taskList: {
    ...scrollBarStyles(theme),
    overflowX: 'hidden',
    overflowY: 'scroll',
    margin: '0 auto 0 auto',
    padding: theme.spacing(1),
    paddingTop: theme.spacing(3),
    maxWidth: '600px',
    width: '100%',
    [theme.breakpoints.up('sm')]: {
      width: '80%',
    },
    [theme.breakpoints.up('md')]: {
      width: '50%',
    },
    position: 'relative',
    flexGrow: 1000,
  },

  taskProgress: {
    position: 'absolute',
    margin: '-20px -20px 0 0',
    left: '50%',
    top: '50%',
    width: '40px',
    height: '40px',
  },

  taskCard: {
    margin: '0 auto 0 auto',
    marginBottom: theme.spacing(2),
  },

  fabAddTask: {
    position: 'absolute',
    zIndex: 100,
    bottom: theme.spacing(2),
    right: theme.spacing(2),
    [theme.breakpoints.up('sm')]: {
      bottom: theme.spacing(3.5),
      right: theme.spacing(3.5),
    },
    [theme.breakpoints.up('md')]: {
      bottom: theme.spacing(5),
      right: theme.spacing(5),
    },
  },
}))
