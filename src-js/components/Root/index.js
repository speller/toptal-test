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
import CardContent from '@material-ui/core/CardContent'
import CardActions from '@material-ui/core/CardActions'
import Button from '@material-ui/core/Button'
import SignInDialog from '../SignInDialog'
import {
  apiCreateTask,
  apiDeleteTask,
  apiLoadUsers,
  apiLoadTasks,
  apiSignIn,
  apiSignUp,
  apiUpdateTask,
  apiUpdateUser,
} from './api'
import TaskDialog from '../TaskDialog'
import CircularProgress from '@material-ui/core/CircularProgress'
import moment from 'moment'
import Filter from '../Filter'
import ExportDialog from '../ExportDialog'

const dateFormat = 'YYYY-MM-DD'

function getAuthStorageData(def) {
  const auth = window.localStorage.auth
  return { ...def, ...(auth ? JSON.parse(auth) : {}) }
}

function setAuthStorageData(data) {
  window.localStorage.auth = JSON.stringify(data)
}

function getFilterStorageData(def) {
  const filter = window.localStorage.filter
  return { ...def, ...(filter ? JSON.parse(filter) : {}) }
}

function setFilterStorageData(data) {
  window.localStorage.filter = JSON.stringify(data)
}

function getDefaultTaskState() {
  return {
    tasks: [],
    taskDlgOpen: false,
    inProgress: false,
    dialogTask: {},
  }
}

function authenticateState(result) {
  const { data, accessToken } = result
  return {
    accessToken,
    login: data.login,
    id: data.id,
    isLoggedIn: true,
    signInDlgOpen: false,
    dialogInProgress: false,
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
    users: [],
    signInDlgOpen: false,
    dialogInProgress: false,
  })
  const [taskState, _setTaskState] = useState(getDefaultTaskState())
  const [filterState, _setFilterState] = useState({
    ... getFilterStorageData({
      dateFrom: moment().subtract(1, 'months').format(dateFormat),
      dateTo: moment().format(dateFormat),
    }),
    hoursPerDay: 0,
  })
  const [exportDlgOpen, setExportDlgOpen] = useState(false)

  const setAuthState = (props) => {
    _setAuthState({...authState, ...props})
  }
  const setTaskState = (props) => {
    _setTaskState({...taskState, ...props})
  }
  const setFilterState = (props) => {
    _setFilterState({...filterState, ...props})
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
      users: [],
    })
  }

  useEffect(() => {
    setAuthStorageData({
      isLoggedIn: authState.isLoggedIn,
      accessToken: authState.accessToken,
      login: authState.login,
      id: authState.id,
      users: [],
    })
  }, [authState.isLoggedIn])

  useEffect(() => {
    handleLoadTasks()
  }, [authState.isLoggedIn, filterState.dateFrom, filterState.dateTo])

  useEffect(() => {
    setFilterStorageData(filterState)
  }, [filterState.dateFrom, filterState.dateTo])

  useEffect(() => {
    handleUpdateWorkingHours()
  }, [filterState.hoursPerDay])

  const handleSignIn = async(login, password) => {
    setAuthState({ dialogInProgress: true })
    const data = await apiSignIn(login, password, setAuthState)
    if (data) {
      setAuthState(authenticateState(data))
      setFilterState({
        hoursPerDay: data.data.workingHoursPerDay,
      })
    } else {
      setAuthState({ dialogInProgress: false })
    }
  }

  const handleSignUp = async(login, password, role) => {
    setAuthState({ dialogInProgress: true })
    const data = await apiSignUp(login, password, role)
    if (data) {
      setAuthState(authenticateState(data))
      setFilterState({
        hoursPerDay: data.data.workingHoursPerDay,
      })
    } else {
      setAuthState({ dialogInProgress: false })
    }
  }

  const handleLoadTasks = async() => {
    if (!authState.isLoggedIn || taskState.inProgress) {
      return
    }
    setTaskState({ inProgress: true })
    const tasks = await apiLoadTasks(filterState.dateFrom, filterState.dateTo, authState.accessToken)
    if (tasks !== false) {
      const users = await apiLoadUsers(authState.accessToken)
      if (users !== false) {
        setAuthState({
          users,
        })
        setTaskState({
          tasks,
          inProgress: false,
        })
        setFilterState({
          hoursPerDay: lookupUser(authState.id, users).workingHoursPerDay,
        })
      }
    } else {
      setTaskState({ inProgress: false })
    }
  }

  const handleTaskDlgClose = () => {
    setTaskState({taskDlgOpen: false})
  }

  const handleTaskDlgSubmit = async(id, title, date, duration) => {
    setTaskState({ inProgress: true })
    if (id) {
      const result = await apiUpdateTask(
        {
          id,
          title,
          date,
          duration,
        },
        authState.accessToken,
      )
      if (result) {
        const newTasks = [...taskState.tasks]
        const i = newTasks.findIndex(task => task.id === id)
        newTasks[i] = {
          ...newTasks[i],
          title,
          date,
          duration,
        }
        setTaskState({
          tasks: newTasks,
          inProgress: false,
          taskDlgOpen: false,
          dialogTask: {},
        })
      } else {
        setTaskState({ inProgress: false })
      }

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
        newTasks.push({
          ...taskState.dialogTask,
          id: newId,
          userId: authState.id,
          title,
          date,
          duration,
        })
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

  const handleEditTask = id => {
    const task = taskState.tasks.find(task => task.id === id)
    setTaskState({
      taskDlgOpen: true,
      dialogTask: {...task},
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

  const handleFilter = async(hoursPerDay, dateFrom, dateTo) => {
    setFilterState({
      hoursPerDay,
      dateFrom,
      dateTo,
    })
  }

  const handleUpdateWorkingHours = async() => {
    if (!authState.isLoggedIn || authState.users.length === 0 || filterState.hoursPerDay === 0) {
      return
    }
    await apiUpdateUser(authState.id, filterState.hoursPerDay, authState.accessToken)
    handleUpdateUsers()
  }

  const handleUpdateUsers = async() => {
    if (!authState.isLoggedIn) {
      return
    }
    const users = await apiLoadUsers(authState.accessToken)
    if (users !== false) {
      setAuthState({
        users,
      })
    }
  }

  const handleExport = () => {
    setExportDlgOpen(true)
  }

  const handleExportClose = () => {
    setExportDlgOpen(false)
  }

  const renderTasksContext = {
    sumHoursPerDay: {},
    lastGroup: '',
  }

  taskState.tasks.forEach(task => {
    const groupKey = `${task.userId}-${task.date}`
    if (!renderTasksContext.sumHoursPerDay[groupKey]) {
      renderTasksContext.sumHoursPerDay[groupKey] = 0
    }
    renderTasksContext.sumHoursPerDay[groupKey] += task.duration
  })

  const lookupUser = (id, users) => (users ? users : authState.users).find(user => user.id === id)

  const renderTask = task => {
    const groupKey = `${task.userId}-${task.date}`
    const printDate = groupKey !== renderTasksContext.lastGroup
    if (printDate) {
      renderTasksContext.lastGroup = groupKey
    }
    const taskUser = lookupUser(task.userId)
    const cardBkColor =
      renderTasksContext.sumHoursPerDay[groupKey] > taskUser.workingHoursPerDay
        ? '#caffca'
        : 'mistyrose'

    return (
      <React.Fragment key={task.id}>
        {printDate &&
        <Typography variant="h5">
          {task.date} {taskUser.login} ({taskUser.workingHoursPerDay})
        </Typography>}
        <Card className={classes.taskCard}>
          <CardContent style={{backgroundColor: cardBkColor}}>
            <Typography gutterBottom variant="h5" component="h2">
              #{task.id} {task.title}
            </Typography>
            <Typography variant="body2" color="textSecondary" component="p">
              Duration: {task.duration} h.
            </Typography>
          </CardContent>
          <CardActions>
            <Button size="small" color="primary" onClick={() => handleEditTask(task.id)}>
              Edit
            </Button>
            <Button size="small" color="primary" onClick={() => handleDeleteTask(task.id)}>
              Delete
            </Button>
          </CardActions>
        </Card>
      </React.Fragment>
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
        <Filter
          hoursPerDay={filterState.hoursPerDay}
          dateFrom={filterState.dateFrom}
          dateTo={filterState.dateTo}
          disabled={taskState.inProgress}
          onFilter={handleFilter}
        />
      </AppBar>

      {authState.isLoggedIn &&
      <Fab
        color="primary"
        className={classes.fabAddTask}
        onClick={handleAddTask}
        disabled={!authState.isLoggedIn || taskState.inProgress || taskState.taskDlgOpen}
        title="Add task"
      >
        <FontAwesomeIcon icon="plus"/>
      </Fab>}

      {authState.isLoggedIn &&
      <Fab
        size="small"
        color="secondary"
        className={classes.fabExport}
        onClick={handleExport}
        disabled={!authState.isLoggedIn || taskState.inProgress || taskState.taskDlgOpen}
        title="Export"
      >
        <FontAwesomeIcon icon="file-export"/>
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
          {exportDlgOpen &&
          <ExportDialog
            open
            tasks={taskState.tasks}
            onClose={handleExportClose}
          />}
        </React.Fragment>}
      </div>
    </React.Fragment>
  )
}

Root.propTypes = {
}

export default Root
