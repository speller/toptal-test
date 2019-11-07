import React, { useState } from 'react'
import PropTypes from 'prop-types'
import Button from '@material-ui/core/Button'
import makeStyles from '@material-ui/core/styles/makeStyles'
import TextField from '@material-ui/core/TextField'
import { lighten } from '@material-ui/core/styles'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'

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
    width: '8rem',
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

  const handleChangeHoursPerDay = event => {
    setState({hoursPerDay: event.target.value})
  }

  const handleChangeDateFrom = event => {
    setState({dateFrom: event.target.value})
  }

  const handleChangeDateTo = event => {
    setState({dateTo: event.target.value})
  }

  const handleFilter = event => {
    event.preventDefault()
    if (!disabled) {
      onFilter(state.hoursPerDay, state.dateFrom, state.dateTo)
    }
  }

  const handleFormSubmit = event => {
    handleFilter(event)
  }

  return (
    <form className={classes.filtersBlock} onSubmit={handleFormSubmit}>
      <TextField
        className={classes.filterInput}
        label="Hours per day"
        margin="dense"
        onChange={handleChangeHoursPerDay}
        value={state.hoursPerDay}
      />
      <TextField
        className={classes.filterInput}
        label="Date from"
        margin="dense"
        onChange={handleChangeDateFrom}
        value={state.dateFrom}
      />
      <TextField
        className={classes.filterInput}
        label="Date to"
        margin="dense"
        onChange={handleChangeDateTo}
        value={state.dateTo}
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
