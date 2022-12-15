import { Outlet } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
	// TODO
	return (
		<div>
			<HeadBar selectedKeys="course" />
			<Outlet />
			<FooterBar />
		</div>
	);
};

export default Widget;
