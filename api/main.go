package main

import (
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/iapt-platform/mint"
	"fmt"
)

func main() {

	opt, err := pg.ParseURL("postgres://postgres:@127.0.0.1:5433/mint?sslmode=disable")
	if err != nil {
		panic(err)
	}
	fmt.Println("pg connectd")
	db := pg.Connect(opt)
	defer db.Close()

	rt := gin.Default()

	// TODO 在这里进行http mount

	rt.GET("/demo/user/:id", mint.GetDemo(db))
	//rt.POST("/demo/sign-in", mint.LoginDemo(db))
	rt.POST("/demo/user",mint.PostDemo(db))
	rt.PUT("/demo/user",mint.PutDemo(db))
	rt.PATCH("/demo/user/:id",mint.PatchDemo(db))
	rt.DELETE("/demo/user/:id",mint.DeleteDemo(db))
	rt.Run()
}
