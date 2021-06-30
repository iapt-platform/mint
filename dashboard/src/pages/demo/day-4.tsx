import { Layout,Menu,Breadcrumb,Table,Tag,Space } from "antd";
import { UserOutlined,LaptopOutlined,NotificationOutlined } from "@ant-design/icons";
import { Footer } from "antd/lib/layout/layout";
import ReactDOM from "react-dom";

const {SubMenu} = Menu;
const {Header,Content,Sider}=Layout;



const dataSource=[
    {
        key:'1',
        name:'name1',
        age:32,
        adress:"西湖南路"
    },
    {
        key:'2',
        name:'name2',
        age:34,
        adress:'西湖公园'
    }

]

const columns=[
    {
        title:'Name',
        dataIndex:'name',
        key:'name',
        render:text=><a>{text}</a>,
    },
    {
        title:'Age',
        dataIndex:'age',
        key:'age',
    },
    {
        title:"地址",
        dataIndex:'adress',
        key:'adress',
    }
]


class App extends React.Component{
    state={
        current:'mail',
    }
         handleclick=e=>{
        console.log('click',e);
        this.setState({current:e.key});
    }

    render(){
    
        const {current}=this.state;
    
        return(
            <Layout>
                <Header className="header">
                    <div className="logo" />
                    <Menu onClick={this.handleclick} theme="dark" mode="horizontal" defaultSelectedKeys={['2']}>
                        <Menu.Item key="1">nav 1</Menu.Item>
                        <Menu.Item key="2">nav 2</Menu.Item>
                        <Menu.Item key="3">nav 3</Menu.Item>
                        <SubMenu key="submenu" icon={<UserOutlined />} title="subnav -1">
                            <Menu.ItemGroup title="group1">
                                <Menu.Item key="4">option1</Menu.Item>
                                <Menu.Item key="5">option2</Menu.Item>
                                <Menu.Item key="6">option3</Menu.Item>                            
                            </Menu.ItemGroup>
                            <Menu.ItemGroup title="group2">
                                <Menu.Item key="7">option1</Menu.Item>
                                <Menu.Item key="8">option2</Menu.Item>
                                <Menu.Item key="9">option3</Menu.Item>                            
                            </Menu.ItemGroup>
                        </SubMenu>
                    </Menu>
                </Header>
                <Layout>
                    <Sider width={200} className="site-layout-background">
                        <Menu
                        mode="inline"
                        defaultSelectedKeys={['1']}
                        defaultOpenKeys={['sub1']}
                        style={{height:'100%',borderRight:0}}
                        >
                            <SubMenu key="sub1" icon={<UserOutlined />} title="subnav 1">
                                <Menu.Item key="1">option1</Menu.Item>
                                <Menu.Item key="2">option2</Menu.Item>
                                <Menu.Item key="3">option3</Menu.Item>
                                <Menu.Item key="4">option4</Menu.Item>
                            </SubMenu>
                            <SubMenu key="sub2" icon={<UserOutlined />} title="subnav 2">
                                <Menu.Item key="5">option1</Menu.Item>
                                <Menu.Item key="6">option2</Menu.Item>
                                <Menu.Item key="7">option3</Menu.Item>
                                <Menu.Item key="8">option4</Menu.Item>
                            </SubMenu>
                        </Menu>
                    </Sider>
                    <Layout style={{padding:'0 24px 24px'}}>
                        <Breadcrumb style={{padding:'0 24px 24px'}}>
                            <Breadcrumb.Item>Home</Breadcrumb.Item>
                            <Breadcrumb.Item>List</Breadcrumb.Item>
                            <Breadcrumb.Item>App</Breadcrumb.Item>
                        </Breadcrumb>
                        <Content
                        className="site-layout-background"
                        style={{
                            padding:24,
                            margin:0,
                            minHeight:280,
                        }}>
                            <Table dataSource={dataSource} columns={columns} />
                        </Content>
                    </Layout>
                    <Sider>right</Sider>
                </Layout>
                <Footer>footer</Footer>
            </Layout>
        );
    }

}

ReactDOM.render(<App />,mountNode);
