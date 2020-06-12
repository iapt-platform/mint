import React from 'react';
import {styled,Theme} from '@material-ui/core/styles';
import List from '@material-ui/core/List';
import AddCircleIcon from '@material-ui/icons/AddCircle';
import LibraryBooksIcon from '@material-ui/icons/LibraryBooks';
import GroupIcon from '@material-ui/icons/Group';
import DeleteIcon from '@material-ui/icons/Delete'
import ListItem from '@material-ui/core/ListItem';
import {ListItemIcon as _ListItemIcon} from '@material-ui/core';
import ListItemText from '@material-ui/core/ListItemText';

export type WikipaliNavbarProps = {
  onAddDocument?: () => void
  onMyDocuments?: () => void
  onGroups?: () => void
  onTrash?: () => void
}

const ListItemIcon = styled(_ListItemIcon)(({theme}) => ({
  color: 'inherit'
}))

export function WikipaliNavbar(props: WikipaliNavbarProps) {
  return (
    <List>
      <ListItem button onClick={props.onAddDocument}>
        <ListItemIcon><AddCircleIcon /></ListItemIcon>
        <ListItemText primary="Add document" />
      </ListItem>
      <ListItem button onClick={props.onMyDocuments}>
        <ListItemIcon><LibraryBooksIcon /></ListItemIcon>
        <ListItemText primary="My documents" />
      </ListItem>
      <ListItem button onClick={props.onGroups}>
        <ListItemIcon><GroupIcon /></ListItemIcon>
        <ListItemText primary="Groups" />
      </ListItem>
      <ListItem button onClick={props.onTrash}>
        <ListItemIcon><DeleteIcon /></ListItemIcon>
        <ListItemText primary="Trash" />
      </ListItem>
    </List>
  );
}
