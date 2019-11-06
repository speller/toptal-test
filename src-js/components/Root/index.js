import React, { useState } from 'react'
import Typography from '@material-ui/core/Typography'
import IconButton from '@material-ui/core/IconButton'
import Badge from '@material-ui/core/Badge'
import AppBar from '@material-ui/core/AppBar'
import Toolbar from '@material-ui/core/Toolbar'
import CssBaseline from '@material-ui/core/CssBaseline'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import useStyles from './styles'
import Fab from '@material-ui/core/Fab'

/**
 * Root page component
 * @param props
 * @returns {*}
 * @constructor
 */
function Root(props) {
  const [isLoggedIn, setIsLoggedIn] = useState(false)
  const [anchorEl, setAnchorEl] = useState(null)
  const [saleRent, setSaleRent] = useState(0)
  const classes = useStyles()
  const isProfileMenuOpen = Boolean(anchorEl)

  const handleProfileMenuOpen = event => {
    setAnchorEl(event.currentTarget)
  }

  const handleProfileMenuClose = event => {
    setAnchorEl(null)
  }

  function handleSaleRentSwitch(event, newValue) {
    setSaleRent(newValue)
  }

  return (
    <React.Fragment>
      <CssBaseline />
      <AppBar position="absolute">
        <Toolbar className={classes.appToolBar}>
          <Typography className={classes.title} variant="h6" color="inherit" noWrap>
            Toptal<br />
            <i>test project</i>
          </Typography>

          <div className={classes.grow} />

          {!isLoggedIn && <div>
            <IconButton color="inherit" title="Sign In or Sign Up">
              <FontAwesomeIcon icon="sign-in-alt" />
            </IconButton>
          </div>}
          {isLoggedIn && <div>
            <div>
              <Typography variant="body1" color="inherit" noWrap>
                You're logged in as
              </Typography>
            </div>
            <IconButton color="inherit" title="Sign Out">
              <FontAwesomeIcon icon="sign-out" />
            </IconButton>
          </div>}
        </Toolbar>
      </AppBar>

      <Fab color="primary" className={classes.fabFilters}>
        <Badge badgeContent={17} color="secondary" classes={{badge: classes.fabFilterBadge}}>
          <FontAwesomeIcon icon="filter" />
        </Badge>
      </Fab>

      <div className={classes.root}>
        <div className={classes.contentPadding}>
          padding
        </div>

        <div className={classes.mapAndList}>
          <div className={classes.propList}>
            <div style={{height: '2000px', width: '200px', border: '1px solid black', backgroundColor: 'lightgray'}}>Block</div>
          </div>
          <div className={classes.map}>
            map
          </div>
        </div>

      </div>
    </React.Fragment>
  )
}

Root.propTypes = {
}

export default Root
