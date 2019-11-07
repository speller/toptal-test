import React, { useState } from 'react'
import PropTypes from 'prop-types'
import Button from '@material-ui/core/Button'
import Dialog from '@material-ui/core/Dialog'
import DialogActions from '@material-ui/core/DialogActions'
import DialogContent from '@material-ui/core/DialogContent'
import withMobileDialog from '@material-ui/core/withMobileDialog'
import makeStyles from '@material-ui/core/styles/makeStyles'
import TextField from '@material-ui/core/TextField'
import AppBar from '@material-ui/core/AppBar'
import Tabs from '@material-ui/core/Tabs'
import SwipeableViews from 'react-swipeable-views'
import Box from '@material-ui/core/Box'
import Tab from '@material-ui/core/Tab'
import Container from '@material-ui/core/Container'

const useStyles = theme => ({
  actions: {
    justifyContent: 'center',
    paddingTop: theme.spacing(5),
  },
  cancelButton: {
    marginRight: theme.spacing(3),
  },
})

function TabPanel(props) {
  const { children, value, index, ...other } = props

  return (
    <Container
      component="div"
      role="tabpanel"
      hidden={value !== index}
      id={`full-width-tabpanel-${index}`}
      aria-labelledby={`full-width-tab-${index}`}
      {...other}
    >
      <Box p={3}>{children}</Box>
    </Container>
  )
}

function SignInDialog(props) {
  const { onClose, open, onSignIn, onSignUp } = props
  const classes = makeStyles(useStyles)()
  const [value, setValue] = useState(0)

  const handleChange = (event, newValue) => {
    setValue(newValue)
  }

  const handleChangeIndex = index => {
    setValue(index)
  }

  return (
    <div>
      <Dialog
        open={open}
        onClose={onClose}
        // aria-labelledby="responsive-dialog-title"
      >
        {/*<DialogTitle id="responsive-dialog-title">{'Use Google\'s location service?'}</DialogTitle>*/}
        <DialogContent>
          <AppBar position="static" color="default">
            <Tabs
              value={value}
              onChange={handleChange}
              indicatorColor="primary"
              textColor="primary"
              variant="fullWidth"
              aria-label="Sign In or Sign Up"
            >
              <Tab label="Sign In" />
              <Tab label="Sign Up" />
            </Tabs>
          </AppBar>
          <SwipeableViews
            index={value}
            onChangeIndex={handleChangeIndex}
          >
            <TabPanel value={value} index={0} className={classes.fieldContainer}>
              <TextField
                className={classes.textField}
                autoFocus
                label="Login"
                margin="normal"
                fullWidth
              />
              <TextField
                className={classes.textField}
                label="Password"
                type="password"
                margin="normal"
                fullWidth
              />
              <DialogActions className={classes.actions}>
                <Button
                  onClick={onClose}
                  variant="contained"
                  color="default"
                  className={classes.cancelButton}
                >
                  Cancel
                </Button>
                <Button onClick={onSignIn} variant="contained" color="primary" autoFocus>
                  Sign In
                </Button>
              </DialogActions>
            </TabPanel>

            <TabPanel value={value} index={1} className={classes.fieldContainer}>
              <TextField
                className={classes.textField}
                autoFocus
                label="Login"
                margin="normal"
                fullWidth
              />
              <TextField
                className={classes.textField}
                label="Password"
                margin="normal"
                type="password"
                fullWidth
              />
              <DialogActions className={classes.actions}>
                <Button
                  onClick={onClose}
                  variant="contained"
                  color="default"
                  className={classes.cancelButton}
                >
                  Cancel
                </Button>
                <Button onClick={onSignUp} variant="contained" color="primary" autoFocus>
                  Sign Up
                </Button>
              </DialogActions>
            </TabPanel>
          </SwipeableViews>
        </DialogContent>
      </Dialog>
    </div>
  )
}

SignInDialog.propTypes = {
  open: PropTypes.bool,
  onClose: PropTypes.func,
  onSignIn: PropTypes.func,
  onSignUp: PropTypes.func,
}

export default withMobileDialog()(SignInDialog)
