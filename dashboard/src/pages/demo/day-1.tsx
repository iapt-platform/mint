import {Link, history} from 'umi';
import {Button} from 'antd';

type IProps = {

}

export default ({}: IProps) => {
    let helloworld: string;
helloworld="name";

interface User{
    name: string;
    id: number;
}
class UserAccount{
    name: string;
    id: number;
    constructor(name:string,id:number){
        this.name = name;
        this.id=id;
    }
}
const user: User={
    name:"Hayes",
    id:0,
}

const user1: User = new UserAccount("Hayes",0);

console.log("user:"+user.name);

function getAdminUser():User{
    let user: User={
        name:"admin",
        id:0,
    }
    return user;
}
function deleteUser(user:User){

}

type MyBool = true | false;
type WindowStates = "open" | "closed" | "minimized";
type LockStates = "locked" | "unlocked";
type PositiveOddNumberUnderTen = 1|3|5|7|9;

function getLength(obj:string|string[]){
    return obj.length;

}

function wrapInArray(obj:string|string[]){
    if(typeof obj === "string"){
        return [obj];
    }
    else{
        return obj;
    }
}

type StringArray= Array<string>;
type NumberArray=Array<number>;
type ObjectWithNameArray=Array<{name:string}>;

interface Backpack<Type>{
    add:(obj:Type)=>void;
    get:()=>Type;
}
/*
declare const backpack: Backpack<string>;
backpack.add("22");
*/
interface Point {
    x:number;
    y:number;
}
function logPoint(p:Point){
    console.log(`x=${p.x},y=${p.y}`);
}
const point = {x:2,y:3};
logPoint(point);

const point3 = {x:3,y:4,z:5};
logPoint(point3);

class VirtualPoint{
    x:number;
    y:number;
    constructor(x:number,y:number){
        this.x=x;
        this.y=y;
    }
}
const newPoint = new VirtualPoint(20,30);
logPoint(newPoint);

  return (
    <div>
      <h1>Demo home page</h1>
      <div>
        <Link to="/demo/items">List items</Link>
        &nbsp;
        <Link to="/demo/items/1/show">Show items 1</Link>
        &nbsp;
        <Link to="/demo/items/2/show">Show items 2</Link>
        &nbsp;
        <Button onClick={()=>history.push('/demo/items/333/edit')}>Edit items 333</Button>
      </div>
    </div>
  );
}
