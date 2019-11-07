import React, { useState } from 'react'
import PropTypes from 'prop-types'
import Button from '@material-ui/core/Button'
import Dialog from '@material-ui/core/Dialog'
import DialogActions from '@material-ui/core/DialogActions'
import DialogContent from '@material-ui/core/DialogContent'
import withMobileDialog from '@material-ui/core/withMobileDialog'
import makeStyles from '@material-ui/core/styles/makeStyles'
import DialogTitle from '@material-ui/core/DialogTitle'
import Typography from '@material-ui/core/Typography'

const useStyles = theme => ({
  actions: {
    justifyContent: 'center',
    paddingTop: theme.spacing(5),
  },

  cancelButton: {
    marginRight: theme.spacing(3),
  },

  table: {
  },

  columnDate: {
    padding: theme.spacing(1),
    borderBottom: '1px solid slategray',
  },

  columnTime: {
    padding: theme.spacing(1),
    borderBottom: '1px solid slategray',
  },

  columnNotes: {
    padding: theme.spacing(1),
    borderBottom: '1px solid slategray',
  },
})

function ExportDialog(props) {
  const { onClose, open, tasks } = props
  const classes = makeStyles(useStyles)()

  const groups = {}

  for (const task of tasks) {
    const groupKey = task.date
    if (!groups[groupKey]) {
      groups[groupKey] = {
        date: task.date,
        total: 0,
        notes: [],
      }
    }
    groups[groupKey].total += task.duration
    groups[groupKey].notes.push(task.title)
  }

  return (
    <Dialog
      open={open}
      onClose={onClose}
      aria-labelledby="form-dialog-title"
      maxWidth="sm"
    >
      <DialogTitle id="form-dialog-title">
        Export
      </DialogTitle>
      <DialogContent>
        <table className={classes.table}>
          <tbody>
            <tr>
              <th className={classes.columnDate}>Date</th>
              <th className={classes.columnTime}>Total Time</th>
              <th className={classes.columnNotes}>Notes</th>
            </tr>
            {Object.keys(groups).map(key => {
              const group = groups[key]
              return <tr key={key}>
                <td className={classes.columnDate}>{group.date}</td>
                <td className={classes.columnTime}>{group.total}</td>
                <td className={classes.columnNotes}>{group.notes.map((note, i) => <div key={i}>{note}</div>)}</td>
              </tr>})}
          </tbody>
        </table>
      </DialogContent>
      <DialogActions className={classes.actions}>
        <Button
          onClick={onClose}
          variant="contained"
          color="default"
          className={classes.cancelButton}
        >
          Close
        </Button>
      </DialogActions>
    </Dialog>
  )
}

ExportDialog.propTypes = {
  open: PropTypes.bool,
  tasks: PropTypes.array.isRequired,
  onClose: PropTypes.func,
}

export default withMobileDialog()(ExportDialog)
