import React from 'react';
import { styled, fade } from "@material-ui/core/styles";
import { InputBase } from "@material-ui/core";
import SearchIcon from '@material-ui/icons/Search';
import { useIntl } from 'react-intl';

const StyledSearchBox = styled('div')(({ theme }) => ({
    position: 'relative',
    borderRadius: theme.shape.borderRadius,
    backgroundColor: fade(theme.palette.common.white, 0.15),
    '&:hover': {
        backgroundColor: fade(theme.palette.common.white, 0.25),
    },
    marginRight: theme.spacing(2),
    marginLeft: 0,
    width: '100%',
    [theme.breakpoints.up('sm')]: {
        marginLeft: theme.spacing(3),
        width: 'auto',
    },
}));


const SearchBoxIcon = styled('div')(({theme}) => ({
    padding: theme.spacing(0, 2),
    height: '100%',
    position: 'absolute',
    pointerEvents: 'none',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center'
  }));

const StyledSearchInput = styled(InputBase)(({ theme }) => ({
    color: 'inherit',
    padding: theme.spacing(1, 1, 1, 0),
    // vertical padding + font size from searchIcon
    paddingLeft: `calc(1em + ${theme.spacing(4)}px)`,
    transition: theme.transitions.create('width'),
    width: '100%',
    [theme.breakpoints.up('md')]: {
        width: '40ch',
    }
}))

export const WikipaliSearchBox = () => {
    const intl = useIntl();
    return (
        <StyledSearchBox>
            <SearchBoxIcon>
                <SearchIcon />
            </SearchBoxIcon>
            <StyledSearchInput
                placeholder={intl.formatMessage({id: 'topbar.search'})}
            />
        </StyledSearchBox>
    )
}