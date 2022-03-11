import { ReactNode } from "react";
import Button from "@mui/material/Button";
import Grid from "@mui/material/Grid";
import Box from "@mui/material/Box";
import Avatar from "@mui/material/Avatar";
import Typography from "@mui/material/Typography";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
import PersonPinOutlinedIcon from "@mui/icons-material/PersonPinOutlined";
import PersonAddAltOutlinedIcon from "@mui/icons-material/PersonAddAltOutlined";
import ConfirmationNumberOutlinedIcon from "@mui/icons-material/ConfirmationNumberOutlined";
import PasswordOutlinedIcon from "@mui/icons-material/PasswordOutlined";
import ListItemIcon from "@mui/material/ListItemIcon";
import ListItemText from "@mui/material/ListItemText";
import List from "@mui/material/List";
import ListItem from "@mui/material/ListItem";
import ListItemButton from "@mui/material/ListItemButton";
import { useNavigate } from "react-router-dom";
import { FormattedMessage } from "react-intl";

import Layout from "../../../layouts/application";

const shared_links = [
    {
        to: "/users/sign-in",
        label: "nut.users.sign-in.title",
        icon: <PersonPinOutlinedIcon />,
    },
    {
        to: "/users/sign-up",
        label: "nut.users.sign-up.title",
        icon: <PersonAddAltOutlinedIcon />,
    },
    {
        to: "/users/confirm",
        label: "nut.users.confirm.title",
        icon: <ConfirmationNumberOutlinedIcon />,
    },
    {
        to: "/users/unlock",
        label: "nut.users.unlock.title",
        icon: <LockOutlinedIcon />,
    },
    {
        to: "/users/forgot-password",
        label: "nut.users.forgot-password.title",
        icon: <PasswordOutlinedIcon />,
    },
];
interface IProps {
    title: string;
    children: ReactNode;
    logo: ReactNode;
    handleSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
}

function Widget({ title, logo, children, handleSubmit }: IProps) {
    // const onSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    //   event.preventDefault();
    //   const data = new FormData(event.currentTarget);
    //   handleSubmit(data);
    // };
    const navigate = useNavigate();
    return (
        <Layout title={title}>
            <Avatar sx={{ bgcolor: "secondary.main" }}>{logo}</Avatar>
            <Typography component="h1" variant="h5">
                {title}
            </Typography>
            <Box component="form" noValidate onSubmit={handleSubmit}>
                <Grid container spacing={2}>
                    {children}
                    <Grid item>
                        <Button type="submit" fullWidth variant="contained">
                            <FormattedMessage id="buttons.submit" />
                        </Button>
                    </Grid>
                    <Grid item>
                        <List>
                            {shared_links.map((it) => (
                                <ListItem key={it.to} disablePadding>
                                    <ListItemButton
                                        onClick={() => navigate(it.to)}
                                    >
                                        <ListItemIcon>{it.icon}</ListItemIcon>
                                        <ListItemText
                                            primary={
                                                <FormattedMessage
                                                    id={it.label}
                                                />
                                            }
                                        />
                                    </ListItemButton>
                                </ListItem>
                            ))}
                        </List>
                    </Grid>
                </Grid>
            </Box>
        </Layout>
    );
}

export default Widget;
