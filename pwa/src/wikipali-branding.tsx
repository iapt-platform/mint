import React from 'react';
import { styled, fade } from '@material-ui/core/styles';
import wikipaliIconSrc from './wikipali-logo.svg';

const WikipaliLogo = styled(() => <img src={wikipaliIconSrc} />)({
    height: '50px',
    verticalAlign: 'middle'
});

const WikipaliText = styled('span')({
fontFamily: "noto-sans, sans-serif",
fontStyle: "normal",
fontWeight: 400,
fontSize: "20px",
"&::before": { content: '"wikipali"' }
});

const StudioText = styled('span')({
fontFamily: "noto-sans, sans-serif",
fontStyle: "normal",
fontWeight: 200,
fontSize: "20px",
"&::before": { content: '"studio"' }
});

const StyledContainer = styled('div')({
    display: 'inline-flex',
    alignItems: 'center'
})

export const WikipaliBranding = () => (
    <StyledContainer>
        <WikipaliLogo />
        <WikipaliText />
        <StudioText />
    </StyledContainer>
)