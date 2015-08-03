<?php namespace WP_CLI_Dotenv_Command;

use WP_CLI;
use WP_CLI_Command;

/**
 * Manage WordPress salts in .env format
 * @package WP_CLI_Dotenv_Command
 */
class Dotenv_Salts_Command extends WP_CLI_Command
{

    /**
     * Fetch some fresh salts and add them to the environment file if they do not already exist
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis [--file=<path-to-dotenv>]
     *
     * @when before_wp_load
     */
    function generate( $_, $assoc_args )
    {
        $dotenv = get_dotenv_for_write_or_fail($assoc_args);

        $set = $skipped = [ ];

        foreach( Salts::fetch_array() as $key => $value )
        {
            if ( $dotenv->has_key($key) ) {
                WP_CLI::line("The '$key' already exists, skipping.");
                $skipped[ ] = $key;
                continue;
            }

            $dotenv->set($key, $value);
            $set[ ] = $key;
        }

        if ( $set && $dotenv->save() ) {
            WP_CLI::success(count($set) . ' salts set successfully!');
        }

        if ( $skipped ) {
            WP_CLI::warning('Some keys were already defined in the environment file.');
            WP_CLI::line("Use 'wp dotenv salts regenerate' to update them.");
        }
    }

    /**
     * Regenerate salts for the environment file
     *
     * [--file=<path-to-dotenv>]
     * : Path to the environment file.  Default: '.env'
     *
     * @synopsis [--file=<path-to-dotenv>]
     *
     * @when before_wp_load
     */
    function regenerate( $_, $assoc_args )
    {
        $dotenv = get_dotenv_for_write_or_fail($assoc_args);

        if ( ! $salts = Salts::fetch_array() ) return;

        foreach ( $salts as $key => $value ) {
            $dotenv->set($key, $value);
        }

        $dotenv->save();

        WP_CLI::success('Salts regenerated.');
    }

}