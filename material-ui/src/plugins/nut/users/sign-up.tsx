import TextField from "@mui/material/TextField";
import FormControlLabel from "@mui/material/FormControlLabel";
import Checkbox from "@mui/material/Checkbox";
import Grid from "@mui/material/Grid";
import LockOutlinedIcon from "@mui/icons-material/LockOutlined";
import { useIntl } from "react-intl";

import Layout from "./NonSignInLayout";

const Widget = () => {
  const handleSubmit = () => {
    // TODO
    // console.log(data.get("email"));
  };
  const intl = useIntl();
  return (
    <Layout
      logo={<LockOutlinedIcon />}
      title={intl.formatMessage({ id: "nut.users.sign-up.title" })}
      handleSubmit={handleSubmit}
    >
      <Grid item xs={12}>
        <TextField
          required
          fullWidth
          id="realName"
          label={intl.formatMessage({ id: "fields.real-name" })}
          name="lastName"
        />
      </Grid>
      <Grid item xs={12}>
        <TextField
          required
          fullWidth
          id="email"
          label={intl.formatMessage({ id: "fields.email" })}
          name="email"
        />
      </Grid>
      <Grid item xs={12}>
        <TextField
          required
          fullWidth
          name="password"
          label={intl.formatMessage({ id: "fields.password" })}
          type="password"
          id="password"
        />
      </Grid>
      <Grid item xs={12}>
        <TextField
          required
          fullWidth
          name="passwordConfirmation"
          label={intl.formatMessage({ id: "fields.password-confirmation" })}
          type="password"
          id="passwordConfirmation"
        />
      </Grid>
      <Grid item xs={12}>
        <FormControlLabel
          control={<Checkbox value="allowExtraEmails" color="primary" />}
          label="I want to receive inspiration, marketing promotions and updates via email."
        />
      </Grid>
    </Layout>
  );
};

export default Widget;
