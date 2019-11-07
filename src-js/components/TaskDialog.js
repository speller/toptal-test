import React, { useState } from 'react'
import PropTypes from 'prop-types'
import Button from '@material-ui/core/Button'
import Dialog from '@material-ui/core/Dialog'
import DialogActions from '@material-ui/core/DialogActions'
import DialogContent from '@material-ui/core/DialogContent'
import withMobileDialog from '@material-ui/core/withMobileDialog'
import makeStyles from '@material-ui/core/styles/makeStyles'
import TextField from '@material-ui/core/TextField'
import LinearProgress from '@material-ui/core/LinearProgress'
import DialogTitle from '@material-ui/core/DialogTitle'

const useStyles = theme => ({
  actions: {
    justifyContent: 'center',
    paddingTop: theme.spacing(5),
  },
  cancelButton: {
    marginRight: theme.spacing(3),
  },
})

function TaskDialog(props) {
  const { onClose, onSubmit, inProgress, open, id, title, duration, date } = props
  const classes = makeStyles(useStyles)()
  const [state, _setState] = useState({
    title,
    titleError: '',
    duration,
    durationError: '',
    date,
    dateError: '',
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

  const validateTitle = (value) => {
    return validateField('title', value, notEmptyValidator)
  }

  const validateDate = (value) => {
    return validateField('date', value, notEmptyValidator)
  }

  const validateDuration = (value) => {
    return validateField('duration', value, positiveNumberValidator)
  }

  const handleChangeTitle = event => {
    setState(validateTitle(event.target.value).state)
  }

  const handleChangeDuration = event => {
    setState(validateDuration(event.target.value).state)
  }

  const handleChangeDate = event => {
    setState(validateDate(event.target.value).state)
  }

  const handleSubmit = event => {
    event.preventDefault()
    if (!hasErrors && !validateAllFields()) {
      onSubmit(id, state.title, state.date, state.duration)
    } else {
      alert('Some fields contain errors')
    }
  }

  const handleFormSubmit = event => {
    handleSubmit(event)
  }

  const notEmpty = value => value.length > 0

  const notEmptyValidator = value => [value && value.length > 0, 'Must not be empty']
  const positiveNumberValidator = value => [value > 0, 'Must be higher than 0']

  const hasErrors = notEmpty(state.titleError) || notEmpty(state.durationError) || notEmpty(state.dateError)

  const validateAllFields = () => {
    let result = true, resultState = {}, fieldResult
    fieldResult = validateTitle(state.title)
    result = result && !fieldResult.valid
    resultState = {...resultState, ...fieldResult.state}

    fieldResult = validateDuration(state.duration)
    result = result && !fieldResult.valid
    resultState = {...resultState, ...fieldResult.state}

    fieldResult = validateDuration(state.duration)
    result = result && !fieldResult.valid
    resultState = {...resultState, ...fieldResult.state}

    setState(resultState)
    return result
  }

  return (
    <Dialog
      open={open}
      onClose={onClose}
      aria-labelledby="form-dialog-title"
    >
      <form onSubmit={event => handleFormSubmit(event)}>
        <DialogTitle id="form-dialog-title">
          {id ? 'Update Task' : 'Create Task'}
        </DialogTitle>
        <DialogContent>
          <TextField
            label="Date"
            margin="normal"
            fullWidth
            onChange={handleChangeDate}
            value={state.date}
            disabled={inProgress}
            error={notEmpty(state.dateError)}
            helperText={state.dateError}
          />
          <TextField
            autoFocus
            label="Title"
            margin="normal"
            fullWidth
            onChange={handleChangeTitle}
            value={state.title}
            disabled={inProgress}
            error={notEmpty(state.titleError)}
            helperText={state.titleError}
          />
          <TextField
            label="Duration"
            margin="normal"
            fullWidth
            onChange={handleChangeDuration}
            value={state.duration}
            disabled={inProgress}
            error={notEmpty(state.durationError)}
            helperText={state.duration}
          />
        </DialogContent>
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
            onClick={handleSubmit}
            variant="contained"
            color="primary"
            autoFocus
            type="submit"
            disabled={inProgress}
          >
            {id ? 'Update Task' : 'Create Task'}
          </Button>
        </DialogActions>
        {inProgress &&
        <LinearProgress color="primary"/>}
      </form>
    </Dialog>
  )
}

TaskDialog.propTypes = {
  open: PropTypes.bool,
  id: PropTypes.number,
  title: PropTypes.string,
  duration: PropTypes.number,
  onClose: PropTypes.func,
  onSubmit: PropTypes.func,
  inProgress: PropTypes.bool,
}

export default withMobileDialog()(TaskDialog)
