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

  saleRentBtn: {
    minHeight: '40px !important',
  },

  saleRentTabs: {
    minHeight: '40px !important',
  },

  saleRentSelectedBtn: {
    backgroundColor: lighten(theme.customTheme.mainColor2, 0.5),
    borderRadius: theme.spacing(0.5),
  },

  sectionDesktop: {
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

  mapAndList: {
    flexGrow: 1,
    height: '100%',
    width: '100%',
    display: 'flex',
    overflow: 'hidden',
  },

  propList: {
    ...scrollBarStyles(theme),
    overflowX: 'hidden',
    overflowY: 'scroll',
    // maxWidth: '400px',
    width: '25%',
    // [theme.breakpoints.up('sm')]: {
    //   width: '20%',
    // },
  },

  map: {
    backgroundColor: 'beige',
    flexGrow: 1,
  },

  fabFilters: {
    position: 'absolute',
    bottom: theme.spacing(2),
    right: theme.spacing(2),
  },

  fabFilterBadge: {
    top: '-75%',
    right: '-75%',
    backgroundColor: theme.palette.background.default,
    border: '1px solid',
    borderColor: theme.palette.primary.main,
    color: theme.palette.text.primary,
  },
}))
