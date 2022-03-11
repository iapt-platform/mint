import Typography from "@mui/material/Typography";
import Link from "@mui/material/Link";
import GitHubIcon from "@mui/icons-material/GitHub";
import HomeIcon from "@mui/icons-material/Home";
import { FormattedMessage } from "react-intl";
import Stack from "@mui/material/Stack";
import { Helmet } from "react-helmet-async";

import { refresh, selectSiteInfo } from "../reducers/site-info";
import { useAppSelector, useAppDispatch } from "../hooks";
import { set as setLocale } from "../locales";

interface IProps {
    title: string;
}

const Widget = (props: IProps) => {
    const site = useAppSelector(selectSiteInfo);
    const dispatch = useAppDispatch();
    if (site.languages.length === 0) {
        dispatch(
            refresh({
                title: "demo",
                subhead: "test",
                copyright: `${new Date().getFullYear()}`,
                languages: ["en-US"],
            })
        );
    }
    return (
        <Stack spacing={2} direction="row">
            <Helmet>
                <title>
                    {props.title} | {site.title}
                </title>
            </Helmet>
            <Typography variant="body2" color="text.secondary" align="center">
                ©{site.copyright}
            </Typography>
            <Link
                color="inherit"
                target="_blank"
                href="https://github.com/iapt-platform/mint"
            >
                <GitHubIcon />
            </Link>
            <Link color="inherit" target="_blank" href="/">
                <HomeIcon />
            </Link>
            {site.languages.map((it) => (
                <Link
                    onClick={() => {
                        setLocale(it);
                        window.location.reload();
                    }}
                    key={it}
                >
                    <FormattedMessage id={`languages.${it}`} />
                </Link>
            ))}
        </Stack>
    );
};

export default Widget;
