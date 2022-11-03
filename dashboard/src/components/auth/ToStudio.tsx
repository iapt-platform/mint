import { Button } from "antd";
import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

const Widget = () => {
	const [userName, setUserName] = useState("");
	const user = useAppSelector(_currentUser);
	useEffect(() => {
		setUserName(user ? user.realName : "");
	}, [user]);
	return (
		<>
			<Link to={`/studio/${userName}/home`}>
				<Button type="primary">藏经阁</Button>
			</Link>
		</>
	);
};

export default Widget;
