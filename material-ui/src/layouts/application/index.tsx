import { ReactNode } from "react";
import CssBaseline from "@mui/material/CssBaseline";
import Box from "@mui/material/Box";
import Container from "@mui/material/Container";
import { createTheme, ThemeProvider } from "@mui/material/styles";
import { HelmetProvider } from "react-helmet-async";

import Copyright from "../Copyright";

const theme = createTheme();

interface IProps {
  title: string;
  children: ReactNode;
}

function Widget({ title, children }: IProps) {
  return (
    <HelmetProvider>
      <ThemeProvider theme={theme}>
        <Container component="main" maxWidth="xs">
          <CssBaseline />
          <Box
            sx={{
              marginTop: 8,
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
            }}
          >
            {children}
          </Box>
          <Copyright title={title} />
        </Container>
      </ThemeProvider>
    </HelmetProvider>
  );
}

export default Widget;
