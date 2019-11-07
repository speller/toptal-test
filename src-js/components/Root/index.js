import React, { useEffect, useState } from 'react'
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
import SignInDialog from '../SignInDialog'
import { apiCreateTask, apiDeleteTask, apiLoadTasks, apiSignIn, apiSignUp, } from './api'
import TaskDialog from '../TaskDialog'
import CircularProgress from '@material-ui/core/CircularProgress'
import moment from 'moment'

const dateFormat = 'YYYY-MM-DD'

function getAuthStorageData(def) {
  const auth = window.localStorage.auth
  return { ...def, ...(auth ? JSON.parse(auth) : {}) }
}

function setAuthStorageData(data) {
  window.localStorage.auth = JSON.stringify(data)
}

function getDefaultTaskState() {
  return {
    tasks: [],
    taskDlgOpen: false,
    inProgress: false,
    dialogTask: {},
  }
}

/**
 * Root page component
 * @param props
 * @returns {*}
 * @constructor
 */
function Root(props) {
  const classes = useStyles()
  const [authState, _setAuthState] = useState({
    ...getAuthStorageData({
      isLoggedIn: false,
      accessToken: null,
      login: null,
      id: null,
    }),
    signInDlgOpen: false,
    dialogInProgress: false,
  })

  const [taskState, _setTaskState] = useState(getDefaultTaskState())

  const setAuthState = (props) => {
    _setAuthState({...authState, ...props})
  }

  const setTaskState = (props) => {
    _setTaskState({...taskState, ...props})
  }

  const handleSignInDlgClose = () => {
    setAuthState({signInDlgOpen: false})
  }

  const handleSignInClick = async event => {
    setAuthState({signInDlgOpen: true})
  }

  const handleSignOutClick = async event => {
    setTaskState(getDefaultTaskState())
    setAuthState({
      isLoggedIn: false,
      accessToken: null,
      login: null,
      id: null,
    })
  }

  useEffect(() => {
    handleLoadTasks()
    setAuthStorageData({
      isLoggedIn: authState.isLoggedIn,
      accessToken: authState.accessToken,
      login: authState.login,
      id: authState.id,
    })
  }, [authState.isLoggedIn])

  const handleSignIn = (login, password) => {
    apiSignIn(login, password, setAuthState)
  }

  const handleSignUp = (login, password, role) => {
    apiSignUp(login, password, role, setAuthState)
  }

  const handleLoadTasks = () => {
    if (!authState.isLoggedIn) {
      return
    }
    apiLoadTasks('1900-01-01', '9999-12-31', authState.accessToken, setTaskState)
  }

  const handleTaskDlgClose = () => {
    setTaskState({taskDlgOpen: false})
  }

  const handleTaskDlgSubmit = async(id, title, date, duration) => {
    setTaskState({ inProgress: true })
    if (id) {

    } else {
      const newId = await apiCreateTask(
        {
          title,
          date,
          duration,
        },
        authState.accessToken,
      )
      if (newId !== null) {
        const newTasks = [...taskState.tasks]
        newTasks.push({...taskState.dialogTask, id: newId})
        setTaskState({
          tasks: newTasks,
          inProgress: false,
          taskDlgOpen: false,
          dialogTask: {},
        })
      } else {
        setTaskState({ inProgress: false })
      }
    }
  }

  const handleAddTask = () => {
    setTaskState({
      taskDlgOpen: true,
      dialogTask: {
        id: 0,
        title: '<New Task>',
        date: moment().format(dateFormat),
        duration: 1,
      },
    })
  }

  const handleDeleteTask = async(id) => {
    if (!id) {
      return
    }
    if (confirm(`Are you sure deleting task #${id} ?`)) {
      setTaskState({ inProgress: true })
      if (await apiDeleteTask(id, authState.accessToken)) {
        setTaskState({
          inProgress: false,
          tasks: taskState.tasks.filter(task => task.id !== id),
          dialogTask: {},
        })
      } else {
        setTaskState({ inProgress: false })
      }
    }
  }

  const renderTask = task => {
    return (
      <Card key={task.id} className={classes.taskCard}>
        <CardActionArea>
          <CardContent>
            <Typography gutterBottom variant="h5" component="h2">
              #{task.id} {task.title}
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
          <Button size="small" color="primary" onClick={() => handleDeleteTask(task.id)}>
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

          {!authState.isLoggedIn &&
          <React.Fragment>
            <IconButton color="inherit" title="Sign In or Sign Up" onClick={handleSignInClick}>
              <FontAwesomeIcon icon="sign-in-alt"/>
            </IconButton>
          </React.Fragment>}

          {authState.isLoggedIn &&
          <React.Fragment>
            <Typography variant="body1" color="inherit" noWrap className={classes.signedInTitle}>
              You're logged in as {authState.login}
            </Typography>
            <IconButton color="inherit" title="Sign Out" onClick={handleSignOutClick}>
              <FontAwesomeIcon icon="sign-out-alt"/>
            </IconButton>
          </React.Fragment>}
        </Toolbar>
      </AppBar>

      {authState.isLoggedIn &&
      <Fab
        color="primary"
        className={classes.fabAddTask}
        onClick={handleAddTask}
        disabled={!authState.isLoggedIn || taskState.inProgress || taskState.taskDlgOpen}
      >
        <FontAwesomeIcon icon="plus"/>
      </Fab>}

      <div className={classes.root}>
        <div className={classes.contentPadding}>
          padding
        </div>
        {!authState.isLoggedIn &&
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
          {authState.signInDlgOpen &&
          <SignInDialog
            open
            onClose={handleSignInDlgClose}
            onSignIn={handleSignIn}
            onSignUp={handleSignUp}
            inProgress={authState.dialogInProgress}
          />}
        </React.Fragment>}

        {authState.isLoggedIn &&
        <React.Fragment>
          <div className={classes.taskList}>
            <div className={classes.taskProgress}>
              {taskState.inProgress &&
              <CircularProgress size={40} />}
            </div>
            {taskState.tasks.length > 0
              ? taskState.tasks.map(task => renderTask(task))
              : (taskState.inProgress ? null : <Typography variant="body2" style={{textAlign: 'center'}}>No tasks yet</Typography>)}
          </div>
          {taskState.taskDlgOpen &&
          <TaskDialog
            open
            id={taskState.dialogTask.id}
            title={taskState.dialogTask.title}
            date={taskState.dialogTask.date}
            duration={taskState.dialogTask.duration}
            inProgress={taskState.inProgress}
            onClose={handleTaskDlgClose}
            onSubmit={handleTaskDlgSubmit}
          />}
        </React.Fragment>}
      </div>
    </React.Fragment>
  )
}

Root.propTypes = {
}

export default Root
