import { darken } from '@material-ui/core/styles'

export default function scrollbarStyles(theme) {
  return {
    '&::-webkit-scrollbar': {
      width: theme.spacing(1),
    },

    '&::-webkit-scrollbar-thumb': {
      borderRadius: theme.spacing(0.5),
      backgroundColor: darken(theme.palette.background.default, 0.1),
    },
  }
}