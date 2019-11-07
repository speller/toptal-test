import {
  apiCallGet,
  apiCallPost,
  getMessageFromResponse,
  validateApiResult,
} from '../../api'

function alertError(e) {
  alert(e.response ? getMessageFromResponse(e.response) : (e.message ? e.message : e))
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

export async function apiSignIn(login, password, setState) {
  try {
    setState({ dialogInProgress: true })
    const result = await apiCallPost(
      '/signin',
      {
        login,
        password,
      },
    )
    setState(authenticateState(validateApiResult(result)))
    return true
  } catch (e) {
    alertError(e)
    setState({ dialogInProgress: false })
    return false
  }
}

export async function apiSignUp(login, password, role, setState) {
  try {
    setState({ dialogInProgress: true })
    const result = await apiCallPost(
      '/signup',
      {
        login,
        password,
        role,
      },
    )
    setState(authenticateState(validateApiResult(result)))
    return true
  } catch (e) {
    alertError(e)
    setState({ dialogInProgress: false })
    return false
  }
}

export async function apiLoadTasks(dateBegin, dateLast, accessToken, setState) {
  try {
    setState({ inProgress: true })
    const result = await apiCallGet(
      '/task-list',
      {
        dateBegin,
        dateLast,
      },
      accessToken
    )
    const { data } = validateApiResult(result)
    setState({
      tasks: data,
      inProgress: false,
    })
    return true
  } catch (e) {
    alertError(e)
    setState({ inProgress: false })
    return false
  }
}

export async function apiCreateTask(taskData, accessToken) {
  try {
    const result = await apiCallPost(
      '/task-add',
      taskData,
      accessToken
    )
    const { data } = validateApiResult(result)
    return data
  } catch (e) {
    alertError(e)
    return null
  }
}

export async function apiUpdateTask(taskData, accessToken) {
  try {
    const result = await apiCallPost(
      '/task-update',
      taskData,
      accessToken
    )
    validateApiResult(result)
    return true
  } catch (e) {
    alertError(e)
    return false
  }
}

export async function apiDeleteTask(id, accessToken) {
  try {
    const result = await apiCallPost(
      '/task-delete',
      {id},
      accessToken
    )
    validateApiResult(result)
    return true
  } catch (e) {
    alertError(e)
    return false
  }
}
