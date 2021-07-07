package main

import (
	"github.com/gin-gonic/gin"
	"github.com/go-pg/pg/v10"
	"github.com/iapt-platform/mint"
)

func main() {
	opt, err := pg.ParseURL("postgres://postgres:@127.0.0.1:5432/mint?sslmode=disable")
	if err != nil {
		panic(err)
	}

	db := pg.Connect(opt)
	defer db.Close()

	rt := gin.Default()

	// TODO 在这里进行http mount
	rt.GET("/demo/get", mint.GetDemo(db))
	rt.POST("/demo/sign-in", mint.LoginDemo(db))

	rt.Run()
}
