import { Outlet } from "react-router-dom";
import HeadBar from "../../components/studio/HeadBar";
import Footer from "../../components/studio/Footer";

const Widget = () => {
	return (
		<div>
			<HeadBar />
			<Outlet />
			<Footer />
		</div>
	);
};

export default Widget;
