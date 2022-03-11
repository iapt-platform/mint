import { FormattedMessage } from "react-intl";
import { Link } from "react-router-dom";

function Widget() {
  // TODO
  return (
    <div>
      home of <FormattedMessage id="languages.zh-Hant" />
      <ol>
        <li>
          <Link to="/users/sign-in">Sign In</Link>
        </li>
        <li>
          <Link to="/users/sign-up">Sign up</Link>
        </li>
        <li>
          <Link to="/install">install</Link>
        </li>
        <li></li>
      </ol>
    </div>
  );
}

export default Widget;
