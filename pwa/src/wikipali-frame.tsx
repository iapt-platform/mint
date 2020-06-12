import React, { ReactNode } from 'react';
import {styled} from '@material-ui/core/styles';
import { WikipaliTopbar } from './wikipali-topbar';
import { WikipaliNavbar } from './wikipali-navbar';
import {Drawer, Toolbar} from '@material-ui/core';

const StyledFrame = styled('div')({
  display: 'flex'
})

const StyledTopbar = styled(WikipaliTopbar)(({theme}) => ({
  zIndex: theme.zIndex.drawer+1
}))

const StyledDrawer = styled(Drawer)({
  '&, &>*': {
    width: '220px'
  }
})

const ContentArea = styled('main')(({theme}) => ({  
  flexGrow: 1,
  padding: theme.spacing(3)
}))

export type WikipaliFrameProps = {
  children: ReactNode
}

export function WikipaliFrame(props: WikipaliFrameProps) {
  return <StyledFrame>
    <StyledTopbar userLoggedIn={false} />
    <StyledDrawer variant="permanent">
      <Toolbar /> {/* padding */}
      <WikipaliNavbar />
    </StyledDrawer>
    <ContentArea>
      <Toolbar /> {/* padding */}
      {props.children}
    </ContentArea>
  </StyledFrame>;
}
