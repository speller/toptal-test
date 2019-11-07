import React, { useState } from 'react'
import Typography from '@material-ui/core/Typography'
import IconButton from '@material-ui/core/IconButton'
import AppBar from '@material-ui/core/AppBar'
import Toolbar from '@material-ui/core/Toolbar'
import CssBaseline from '@material-ui/core/CssBaseline'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import useStyles from './styles'
import Fab from '@material-ui/core/Fab'
import Card from '@material-ui/core/Card'
import CardActionArea from '@material-ui/core/CardActionArea'
import CardContent from '@material-ui/core/CardContent'
import CardActions from '@material-ui/core/CardActions'
import Button from '@material-ui/core/Button'
import { apiCallPost, getMessageFromResponse, validateApiResult } from '../../api'
import SignInDialog from '../SignInDialog'

/**
 * Root page component
 * @param props
 * @returns {*}
 * @constructor
 */
function Root(props) {
  const classes = useStyles()
  const [state, _setState] = useState({
    isLoggedIn: false,
    accessToken: null,
    login: null,
    tasks: [],
    signInDlgOpen: false,
    dialogInProgress: false,
  })
  const setState = (props) => {
    _setState({...state, ...props})
  }

  const handleSignInDlgClose = () => {
    setState({signInDlgOpen: false})
  }

  const handleSignInClick = async event => {
    setState({signInDlgOpen: true})
  }

  const handleSignOutClick = async event => {
    setState({
      isLoggedIn: false,
      accessToken: null,
      login: null,
    })
  }

  const doSignIn = async(login, password) => {
    try {
      setState({dialogInProgress: true})
      const result = await apiCallPost(
        '/signin',
        {
          login,
          password,
        },
      )
      const {data, accessToken} = validateApiResult(result)
      setState({
        accessToken,
        login: data.login,
        isLoggedIn: true,
        signInDlgOpen: false,
        dialogInProgress: false,
      })
    } catch (e) {
      alert(e.response ? getMessageFromResponse(e.response) : (e.message ? e.message : e))
      setState({dialogInProgress: false})
    }
  }

  const doSignUp = async(login, password, role) => {
    try {
      setState({dialogInProgress: true})
      const result = await apiCallPost(
        '/signup',
        {
          login,
          password,
          role,
        },
      )
      const {data, accessToken} = validateApiResult(result)
      setState({
        accessToken,
        login: data.login,
        isLoggedIn: true,
        signInDlgOpen: false,
        dialogInProgress: false,
      })
    } catch (e) {
      alert(e.response ? getMessageFromResponse(e.response) : (e.message ? e.message : e))
      setState({dialogInProgress: false})
    }
  }

  const renderTask = task => {
    return (
      <Card key={task.id} className={classes.taskCard}>
        <CardActionArea>
          <CardContent>
            <Typography gutterBottom variant="h5" component="h2">
              {task.title}
            </Typography>
            <Typography variant="body2" color="textSecondary" component="p">
              Duration: {task.duration} h.
            </Typography>
          </CardContent>
        </CardActionArea>
        <CardActions>
          <Button size="small" color="primary">
            Edit
          </Button>
          <Button size="small" color="primary">
            Delete
          </Button>
        </CardActions>
      </Card>
    )
  }

  return (
    <React.Fragment>
      <CssBaseline/>
      <AppBar position="absolute">
        <Toolbar className={classes.appToolBar}>
          <Typography className={classes.title} variant="h6" color="inherit" noWrap>
            Toptal<br/>
            <i>test project</i>
          </Typography>

          <div className={classes.grow}/>

          {!state.isLoggedIn &&
          <React.Fragment>
            <IconButton color="inherit" title="Sign In or Sign Up" onClick={handleSignInClick}>
              <FontAwesomeIcon icon="sign-in-alt"/>
            </IconButton>
          </React.Fragment>}

          {state.isLoggedIn &&
          <React.Fragment>
            <Typography variant="body1" color="inherit" noWrap className={classes.signedInTitle}>
              You're logged in as {state.login}
            </Typography>
            <IconButton color="inherit" title="Sign Out" onClick={handleSignOutClick}>
              <FontAwesomeIcon icon="sign-out-alt"/>
            </IconButton>
          </React.Fragment>}
        </Toolbar>
      </AppBar>

      {state.isLoggedIn &&
      <Fab color="primary" className={classes.fabFilters}>
        <FontAwesomeIcon icon="plus"/>
      </Fab>}

      <div className={classes.root}>
        <div className={classes.contentPadding}>
          padding
        </div>

        {!state.isLoggedIn &&
        <React.Fragment>
          <div className={classes.signInBlock}>
            <Typography variant="body1" className={classes.signInTitle}>
              You're not signed in.
            </Typography>
            <Button color="primary" variant="contained" onClick={handleSignInClick}>
              Sign In or Sign Up
              <FontAwesomeIcon icon="sign-in-alt" className={classes.signInBtnIcon}/>
            </Button>
          </div>
          <SignInDialog
            open={state.signInDlgOpen}
            onClose={handleSignInDlgClose}
            onSignIn={doSignIn}
            onSignUp={doSignUp}
            inProgress={state.dialogInProgress}
          />
        </React.Fragment>}

        {state.isLoggedIn && <div className={classes.taskList}>
          {state.tasks.map(task => renderTask(task))}
        </div>}
      </div>
    </React.Fragment>
  )
}

Root.propTypes = {
}

export default Root
