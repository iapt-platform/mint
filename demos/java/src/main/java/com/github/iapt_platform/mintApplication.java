package com.github.iapt_platform;

import io.dropwizard.core.Application;
import io.dropwizard.core.setup.Bootstrap;
import io.dropwizard.core.setup.Environment;

public class mintApplication extends Application<mintConfiguration> {

    public static void main(final String[] args) throws Exception {
        new mintApplication().run(args);
    }

    @Override
    public String getName() {
        return "mint";
    }

    @Override
    public void initialize(final Bootstrap<mintConfiguration> bootstrap) {
        // TODO: application initialization
    }

    @Override
    public void run(final mintConfiguration configuration,
                    final Environment environment) {
        // TODO: implement application
    }

}
