import { Link } from "@fluentui/react/lib/Link";
import { useIntl } from "react-intl";
import { useNavigate } from "react-router-dom";
import { Layer } from "@fluentui/react/lib/Layer";
import { DefaultPalette, Stack, IStackStyles, ICommandBarItemProps, CommandBar, IButtonProps } from "@fluentui/react";
import { IOverflowSetItemProps, OverflowSet } from "@fluentui/react/lib/OverflowSet";
import { IconButton, IButtonStyles } from "@fluentui/react/lib/Button";

import img_banner from "../../assets/library/images/wikipali_logo_library.svg";
//import UiLangSelect from "../general/UiLangSelect";
//import SignInAvatar from "../auth/SignInAvatar";

type IWidgetHeadBar = {
	selectedKeys?: string;
};
const Widget = ({ selectedKeys = "community" }: IWidgetHeadBar) => {
	//Library head bar
	const intl = useIntl(); //i18n
	const navigate = useNavigate();
	// TODO
	const items: ICommandBarItemProps[] = [
		{
			text: intl.formatMessage({ id: "columns.library.community.title" }),
			key: "community",
			onClick: () => navigate("/community/list"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.palicanon.title" }),
			key: "palicanon",
			onClick: () => navigate("/palicanon/list"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.course.title" }),
			key: "course",
			onClick: () => navigate("/course"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.dict.title" }),
			key: "dict",
			onClick: () => navigate("/dict/recent"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.anthology.title" }),
			key: "anthology",
			onClick: () => navigate("/anthology"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.help.title" }),
			key: "help",
			onClick: () => navigate("https://asset-hk.wikipali.org/help/zh-Hans"),
		},
	];

	const overflowItems: ICommandBarItemProps[] = [
		{
			text: intl.formatMessage({ id: "columns.library.palihandbook.title" }),
			key: "palihandbook",
			onClick: () => navigate("https://asset-hk.wikipali.org/handbook/zh-Hans"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.calendar.title" }),
			key: "calendar",
			onClick: () => navigate("/calendar"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.convertor.title" }),
			key: "convertor",
			onClick: () => navigate("/convertor"),
		},
		{
			text: intl.formatMessage({ id: "columns.library.statistics.title" }),
			key: "statistics",
			onClick: () => navigate("/statistics"),
		},
	];
	// Styles definition
	const stackStyles: IStackStyles = {
		root: {
			background: DefaultPalette.themeDark,
		},
	};
	const itemStyles: React.CSSProperties = {
		alignItems: "center",
		color: DefaultPalette.white,
		display: "flex",
		height: 50,
		justifyContent: "center",
	};

	const itemSelected: React.CSSProperties = {
		color: DefaultPalette.white,
		backgroundColor: "gray",
		padding: 6,
	};

	const onRenderItem = (item: IOverflowSetItemProps): JSX.Element => {
		const styles = selectedKeys === item.key ? itemSelected : { padding: 6 };
		return (
			<span style={styles} key={item.key} onClick={item.onClick}>
				{item.text}
			</span>
		);
	};

	const onRenderOverflowButton = (overflowItems: any[] | undefined): JSX.Element => {
		const buttonStyles: Partial<IButtonStyles> = {
			root: {
				minWidth: 0,
				padding: "0 4px",
				alignSelf: "stretch",
				height: "auto",
			},
		};
		return (
			<IconButton
				title="More options"
				styles={buttonStyles}
				menuIconProps={{ iconName: "More" }}
				menuProps={{ items: overflowItems! }}
			/>
		);
	};
	return (
		<Layer style={{ background: DefaultPalette.themeDarker }}>
			<Stack horizontal horizontalAlign="space-between" styles={stackStyles}>
				<span style={itemStyles}>
					<Link to="/">
						<img alt="code" style={{ height: "3em" }} src={img_banner} />
					</Link>
				</span>
				<span style={itemStyles}>
					<OverflowSet
						items={items}
						overflowItems={overflowItems}
						onRenderOverflowButton={onRenderOverflowButton}
						onRenderItem={onRenderItem}
					/>
				</span>
				<span style={itemStyles}>译经楼</span>
			</Stack>
		</Layer>
	);
};

export default Widget;
