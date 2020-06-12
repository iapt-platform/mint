import React from 'react';
import { styled } from '@material-ui/core/styles';
import { AppBar, AppBarProps, Button, Popper, Fade, Paper, Toolbar, IconButton, ClickAwayListener, MenuList, MenuItem } from '@material-ui/core';
import AccountCircleIcon from '@material-ui/icons/AccountCircle';
import MenuIcon from '@material-ui/icons/Menu';
import {
  usePopupState,
  bindTrigger,
  bindPopper,
} from 'material-ui-popup-state/hooks'
import { WikipaliSearchBox } from './wikipali-searchbox';
import { WikipaliBranding } from './wikipali-branding';

const TopbarLeft = styled('div')({
  flex: "1", 
  display: "flex",
  justifyContent: "flex-start",
  alignItems: 'center'
});

const TopbarCenter = styled('div')({
  alignItems: 'center'
});

const TopbarRight = styled('div')({
  flex: "1", 
  display: "flex", 
  justifyContent: "flex-end",
  alignItems: 'center'
});

const SignOutButton = () => {
  return (
    <Button color="inherit">Sign Out</Button>
  );
};

const AccountMenu = () => {
  const popupState = usePopupState({ variant: 'popover', popupId: 'demoMenu' })
  return (
    <div>
      <IconButton color="inherit" {...bindTrigger(popupState)}>
        <AccountCircleIcon />
      </IconButton>
      <Popper {...bindPopper(popupState)} transition>
        {({ TransitionProps }) => (
          <Fade {...TransitionProps} timeout={350}>
            <Paper>
              <ClickAwayListener onClickAway={popupState.close}>
                <MenuList>
                  <MenuItem onClick={popupState.close}>Log out</MenuItem>
                </MenuList>
              </ClickAwayListener>
            </Paper>
          </Fade>
        )}
      </Popper>
    </div>
  )
}

export type WikipaliTopbarProps = {
  userLoggedIn: boolean
}

export function WikipaliTopbar(props: WikipaliTopbarProps&AppBarProps) {
  const {userLoggedIn, ...appBarProps} = props;
  return (
    <AppBar position="fixed" {...appBarProps}>
      <Toolbar>
        <TopbarLeft>
          <IconButton
            edge="start"
            color="inherit"
            aria-label="open drawer"
          >
            <MenuIcon />
          </IconButton>
          <WikipaliBranding />
        </TopbarLeft>
        <TopbarCenter>
          <WikipaliSearchBox />
        </TopbarCenter>
        <TopbarRight>
          {props.userLoggedIn ? <AccountMenu /> : <SignOutButton />}
        </TopbarRight>
      </Toolbar>
    </AppBar>
  );
}
