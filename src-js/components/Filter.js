import React, { useEffect, useState } from 'react'
import PropTypes from 'prop-types'
import Button from '@material-ui/core/Button'
import makeStyles from '@material-ui/core/styles/makeStyles'
import TextField from '@material-ui/core/TextField'
import { lighten } from '@material-ui/core/styles'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import MomentUtils from '@date-io/moment'
import {
  MuiPickersUtilsProvider,
  KeyboardDatePicker,
} from '@material-ui/pickers'

const useStyles = theme => ({
  filtersBlock: {
    borderTop: '1px solid lightgray',
    backgroundColor: lighten(theme.palette.background.default, 0.7),
    color: theme.palette.text.primary,
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
  },

  filterInput: {
    width: '8.5rem',
    marginLeft: theme.spacing(2) + 'px !important',
    marginRight: theme.spacing(2) + 'px !important',
    marginTop: '0 !important',
  },
})

function Filter(props) {
  const { hoursPerDay, dateFrom, dateTo, disabled, onFilter } = props
  const classes = makeStyles(useStyles)()
  const [state, _setState] = useState({
    hoursPerDay,
    dateFrom,
    dateTo,
  })
  const setState = (props) => {
    _setState({ ...state, ...props })
  }
  useEffect(() => {
    setState({ hoursPerDay })
  }, [hoursPerDay])

  const handleChangeHoursPerDay = event => {
    setState({ hoursPerDay: event.target.value })
  }

  const handleChangeDateFrom = event => {
    setState({ dateFrom: event.format('YYYY-MM-DD') })
  }

  const handleChangeDateTo = event => {
    setState({ dateTo: event.format('YYYY-MM-DD') })
  }

  const handleFilter = event => {
    event.preventDefault()
    if (!disabled) {
      onFilter(Number(state.hoursPerDay), state.dateFrom, state.dateTo)
    }
  }

  const handleFormSubmit = event => {
    handleFilter(event)
  }

  return (
    <form className={classes.filtersBlock} onSubmit={handleFormSubmit}>
      <MuiPickersUtilsProvider utils={MomentUtils}>
        <TextField
          className={classes.filterInput}
          label="Hours per day"
          margin="dense"
          onChange={handleChangeHoursPerDay}
          value={state.hoursPerDay}
        />
        <KeyboardDatePicker
          className={classes.filterInput}
          disableToolbar
          variant="inline"
          format="YYYY-MM-DD"
          id="date-picker-inline"
          label="Date from"
          margin="dense"
          onChange={handleChangeDateFrom}
          value={state.dateFrom}
          KeyboardButtonProps={{
            'aria-label': 'change date',
          }}
        />
        <KeyboardDatePicker
          className={classes.filterInput}
          disableToolbar
          variant="inline"
          format="YYYY-MM-DD"
          id="date-picker-inline"
          label="Date to"
          margin="dense"
          onChange={handleChangeDateTo}
          value={state.dateTo}
          KeyboardButtonProps={{
            'aria-label': 'change date',
          }}
        />
        <Button
          variant="contained"
          color="primary"
          size="small"
          startIcon={<FontAwesomeIcon icon="filter"/>}
          onClick={handleFilter}
          type="submit"
        >
          Filter
        </Button>
      </MuiPickersUtilsProvider>
    </form>
  )
}

Filter.propTypes = {
  hoursPerDay: PropTypes.number,
  dateFrom: PropTypes.string,
  dateTo: PropTypes.string,
  onFilter: PropTypes.func,
  disabled: PropTypes.bool,
}

export default Filter
