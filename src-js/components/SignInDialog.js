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
import FormControl from '@material-ui/core/FormControl'
import InputLabel from '@material-ui/core/InputLabel'
import Select from '@material-ui/core/Select'
import MenuItem from '@material-ui/core/MenuItem'
import LinearProgress from '@material-ui/core/LinearProgress'

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
  const { onClose, open, onSignIn, onSignUp, inProgress } = props
  const classes = makeStyles(useStyles)()
  const [state, _setState] = useState({
    tabIndex: 0,
    login: '',
    passwordIn: '',
    passwordUp: '',
    role: 0,
    loginError: '',
    passwordInError: '',
    passwordUpError: '',
  })
  const setState = (props) => {
    _setState({ ...state, ...props })
  }

  const validateField = (field, value, validator) => {
    const [result, error] = validator(value)
    return {
      valid: result,
      state: {
        [`${field}Error`]: result ? '' : error,
        [field]: value,
      },
    }
  }

  const validateLogin = (value) => {
    return validateField('login', value, notEmptyValidator)
  }

  const validatePasswordIn = (value) => {
    return validateField('passwordIn', value, notEmptyValidator)
  }

  const validatePasswordUp = (value) => {
    return validateField('passwordUp', value, notEmptyValidator)
  }

  const handleChangeTabIndex = (event, newValue) => {
    if (newValue === 0) {
      setState({tabIndex: newValue, passwordUp: '', passwordUpError: '', login: '', loginError: ''})
    } else {
      setState({tabIndex: newValue, passwordIn: '', passwordInError: '', login: '', loginError: ''})
    }
  }

  const handleChangeViewIndex = index => {
    handleChangeTabIndex(index)
  }

  const handleChangeLogin = event => {
    setState(validateLogin(event.target.value).state)
  }

  const handleChangePasswordIn = event => {
    setState(validatePasswordIn(event.target.value).state)
  }

  const handleChangePasswordUp = event => {
    setState(validatePasswordUp(event.target.value).state)
  }

  const handleChangeRole = event => {
    setState({ role: event.target.value })
  }

  const handleSignUp = event => {
    event.preventDefault()
    if (!hasErrors && !validateAllFields()) {
      onSignUp(state.login, state.passwordUp, state.role)
    } else {
      alert('Some fields contain errors')
    }
  }

  const handleSignIn = event => {
    event.preventDefault()
    if (!hasErrors && !validateAllFields()) {
      onSignIn(state.login, state.passwordIn)
    } else {
      alert('Some fields contain errors')
    }
  }

  const handleFormSubmit = event => {
    event.preventDefault()
    if (Number(state.tabIndex) === 0) {
      handleSignIn(event)
    } else {
      handleSignUp(event)
    }
  }

  const notEmpty = value => value.length > 0

  const notEmptyValidator = value => [value.length > 0, 'Must not be empty']

  const hasErrors = notEmpty(state.loginError) || notEmpty(state.passwordInError) || notEmpty(state.passwordUpError)

  const validateAllFields = () => {
    let result = true, resultState = {}, fieldResult
    fieldResult = validateLogin(state.login)
    result = result && !fieldResult.valid
    resultState = {...resultState, ...fieldResult.state}
    if (state.tabIndex === 0) {
      fieldResult = validatePasswordIn(state.passwordIn)
      result = result && !fieldResult.valid
      resultState = {...resultState, ...fieldResult.state}
    } else {
      fieldResult = validatePasswordUp(state.passwordUp)
      result = result && !fieldResult.valid
      resultState = {...resultState, ...fieldResult.state}
    }
    setState(resultState)
    return result
  }

  return (
    <Dialog
      open={open}
      onClose={onClose}
    >
      <form onSubmit={event => handleFormSubmit(event)}>
        <DialogContent>
          <AppBar position="static" color="default">
            <Tabs
              value={state.tabIndex}
              onChange={handleChangeTabIndex}
              indicatorColor="primary"
              textColor="primary"
              variant="fullWidth"
              aria-label="Sign In or Sign Up"
            >
              <Tab
                label="Sign In"
                disabled={inProgress}
              />
              <Tab
                label="Sign Up"
                disabled={inProgress}
              />
            </Tabs>
          </AppBar>
          <SwipeableViews
            index={state.tabIndex}
            onChangeIndex={handleChangeViewIndex}
            disabled={inProgress}
          >
            <TabPanel value={state.tabIndex} index={0}>
              <TextField
                autoFocus
                label="Login"
                margin="normal"
                fullWidth
                onChange={handleChangeLogin}
                value={state.login}
                disabled={inProgress}
                error={notEmpty(state.loginError)}
                helperText={state.loginError}
              />
              <TextField
                label="Password"
                type="password"
                margin="normal"
                fullWidth
                value={state.passwordIn}
                onChange={handleChangePasswordIn}
                disabled={inProgress}
                error={notEmpty(state.passwordInError)}
                helperText={state.passwordInError}
              />
              <DialogActions className={classes.actions}>
                <Button
                  onClick={onClose}
                  variant="contained"
                  color="default"
                  className={classes.cancelButton}
                  disabled={inProgress}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleSignIn}
                  variant="contained"
                  color="primary"
                  autoFocus
                  type={state.tabIndex === 0 ? 'submit' : undefined}
                  disabled={inProgress}
                >
                  Sign In
                </Button>
              </DialogActions>
            </TabPanel>

            <TabPanel value={state.tabIndex} index={1}>
              <TextField
                autoFocus
                label="Login"
                margin="normal"
                fullWidth
                onChange={handleChangeLogin}
                value={state.login}
                disabled={inProgress}
                error={notEmpty(state.loginError)}
                helperText={state.loginError}
              />
              <TextField
                label="Password"
                margin="normal"
                type="password"
                fullWidth
                value={state.passwordUp}
                onChange={handleChangePasswordUp}
                disabled={inProgress}
                error={notEmpty(state.passwordUpError)}
                helperText={state.passwordUpError}
              />
              <FormControl
                fullWidth
                disabled={inProgress}
              >
                <InputLabel id="demo-simple-select-label">Role</InputLabel>
                <Select
                  labelId="demo-simple-select-label"
                  id="demo-simple-select"
                  value={state.role}
                  onChange={handleChangeRole}
                >
                  <MenuItem value={0}>User</MenuItem>
                  <MenuItem value={1}>Manager</MenuItem>
                  <MenuItem value={2}>Admin</MenuItem>
                </Select>
              </FormControl>
              <DialogActions className={classes.actions}>
                <Button
                  onClick={onClose}
                  variant="contained"
                  color="default"
                  className={classes.cancelButton}
                  disabled={inProgress}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleSignUp}
                  variant="contained"
                  color="primary"
                  autoFocus
                  disabled={inProgress}
                  type={state.tabIndex === 1 ? 'submit' : undefined}
                >
                  Sign Up
                </Button>
              </DialogActions>
            </TabPanel>
          </SwipeableViews>
          {inProgress &&
          <LinearProgress color="primary"/>}
        </DialogContent>
      </form>
    </Dialog>
  )
}

SignInDialog.propTypes = {
  open: PropTypes.bool,
  onClose: PropTypes.func,
  onSignIn: PropTypes.func,
  onSignUp: PropTypes.func,
  inProgress: PropTypes.bool,
}

export default withMobileDialog()(SignInDialog)
