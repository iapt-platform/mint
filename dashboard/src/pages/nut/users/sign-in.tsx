import SignInForm from "../../../components/nut/users/SignIn";
import SharedLinks from "../../../components/nut/users/NonSignInSharedLinks";
import { Card } from "antd";

const Widget = () => {
	return (
		<div>
			<Card title="登录">
				<div>
					<SignInForm />
				</div>
				<div>
					<SharedLinks />
				</div>
			</Card>
		</div>
	);
};

export default Widget;
